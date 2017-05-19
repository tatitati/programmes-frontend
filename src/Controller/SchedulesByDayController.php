<?php
declare(strict_types = 1);
namespace App\Controller;

use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\Enumeration\NetworkMediumEnum;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\BroadcastsService;
use BBC\ProgrammesPagesService\Service\ServicesService;
use DateInterval;
use DateTimeImmutable;

class SchedulesByDayController extends BaseController
{
    public function __invoke(Pid $pid, ?string $date, ServicesService $servicesService, BroadcastsService $broadcastService)
    {
        if ($date) {
            // Try and create a date from the one provided
            $date = DateTimeImmutable::createFromFormat('Y-m-d', $date);
            if (!$date) {
                throw $this->createNotFoundException('Invalid date');
            }
        } else {
            // Otherwise use now
            $date = ApplicationTime::getTime();
            // TODO blank out the hours. Should that be a method to ApplicationTime?
        }

        $service = $servicesService->findByPidFull($pid);
        if (!$service) {
            throw $this->createNotFoundException('Service not found');
        }

        // Get all services that belong to this network
        $servicesInNetwork = $servicesService->findAllInNetwork($service->getNetwork()->getNid());

        // set day interval time
        $startDate = $date->setTime(0, 0, 0);
        if ($service->getNetwork()->getType() == 'tv') {
            $startDate = $startDate->add(new DateInterval('PT6H'));
        }
        $endDate = $startDate->add(new DateInterval('P1D'));

        // We only need to look for broadcasts if we know the service was available on that day
        // TODO should isAvailableOn be a method on Service?
        $broadcasts = [];
        if ($service->isActiveAt($date)) {
            $broadcasts = $broadcastService->findByServiceAndDateRange($service->getSid(), $startDate, $endDate);
        }

        foreach ($servicesInNetwork as $key => $serviceInNetwork) {
            if (!$serviceInNetwork->isActiveAt($date)) {
                unset($servicesInNetwork[$key]);
            }
        }

        // If there aren't any broadcasts then show a sorry page, that is a 404
        // We don't want to clutter search results with loads of pages that says "sorry no results'
        if (!$broadcasts) {
            // TODO
        }

        $twinService = null;
        if (count($servicesInNetwork) == 2) {
            $otherServices = array_filter($servicesInNetwork, function (Service $sisterService) use ($service) {
                return ($service->getSid() !== $sisterService->getSid());
            });
            $twinService = reset($otherServices);
        }

        return $this->renderWithChrome('schedules/by_day.html.twig', [
            'date' => $date,
            'service' => $service,
            'service_is_tv' => $service->getNetwork()->getMedium() == NetworkMediumEnum::TV,
            'services_in_network' => $servicesInNetwork,
            'twin_service' => $twinService,
            'grouped_broadcasts' => $this->groupBroadcastsByPeriodOfDay($broadcasts, $date),
            'on_air_broadcast' => $this->getOnAirBroadcast($broadcasts),
        ]);
    }

    private function groupBroadcastsByPeriodOfDay(array $broadcasts, DateTimeImmutable $selectedDate): array
    {
        $intervalsDay = [
            'early' => [],
            'morning' => [],
            'afternoon' => [],
            'evening' => [],
            'late' => [],
        ];

        //$prior_broadcast = null;
        foreach ($broadcasts as $broadcast) {
            // // If the end of the prior is earlier than the start of this broadcast
            // // then inject a broadcast gap object.
            // if ($prior_broadcast && $prior_broadcast->end->compare($broadcast->start) == -1) {
            //     $period = $this->_getBroadcastPeriod($prior_broadcast->end, $day, $use_timezones);
            //     $periods_of_day[$period][] = $this->_broadcastGap($prior_broadcast->end, $broadcast->start);
            // }

            $period = $this->getBroadcastPeriodWord($broadcast, $selectedDate);
            $intervalsDay[$period][] = $broadcast;
        }

        return array_filter($intervalsDay);
    }

    private function getBroadcastPeriodWord(Broadcast $broadcast, DateTimeImmutable $selectedDate): string
    {
        $selectedDayStart = $selectedDate->setTime(0, 0, 0);
        $selectedDayEnd = $selectedDate->setTime(23, 59, 59);

        $startBroadcast = $broadcast->getStartAt();
        $startBroadcastHour = $startBroadcast->format('H');

        if ($startBroadcast < $selectedDayEnd && $startBroadcastHour < 6) {
            return 'late';
        }

        if ($startBroadcastHour < 6) {
            return 'early';
        }

        if ($startBroadcastHour < 12) {
            return 'morning';
        }

        if ($startBroadcastHour < 18) {
            return 'afternoon';
        }

        if ($startBroadcastHour <= 23 && $startBroadcast > $selectedDayStart) {
            return 'evening';
        }
    }

    private function getOnAirBroadcast(array $broadcasts): ?Broadcast
    {
        $now = ApplicationTime::getTime();

        foreach ($broadcasts as $broadcast) {
            if ($broadcast->isOnAirAt($now)) {
                return $broadcast;
            }
        }

        return null;
    }
}
