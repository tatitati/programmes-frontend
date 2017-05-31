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
use DateTimeZone;
use Symfony\Component\HttpFoundation\Response;

class SchedulesByDayController extends BaseController
{
    public function __invoke(Pid $pid, ?string $date, ServicesService $servicesService, BroadcastsService $broadcastService)
    {
        $service = $servicesService->findByPidFull($pid);
        if (!$service) {
            throw $this->createNotFoundException('Service not found');
        }

        list($startDateTime, $endDateTime) = $this->getStartAndEndTimes($service->isTV(), $date);

        // Get all services that belong to this network
        $servicesInNetwork = $servicesService->findAllInNetworkActiveOn($service->getNetwork()->getNid(), $startDateTime);

        $twinService = null;
        if (count($servicesInNetwork) == 2) {
            // If there are two services, find the "other" service
            $otherServices = array_filter($servicesInNetwork, function (Service $sisterService) use ($service) {
                return ($service->getSid() !== $sisterService->getSid());
            });
            $twinService = reset($otherServices);
        }

        $viewData = [
            'date' => $startDateTime,
            'service' => $service,
            'services_in_network' => $servicesInNetwork,
            'twin_service' => $twinService,
        ];

        // If the service is not active render a 404
        if (!$service->isActiveAt($startDateTime)) {
            return $this->renderNoSchedule($viewData);
        }

        $broadcasts = $broadcastService->findByServiceAndDateRange($service->getSid(), $startDateTime, $endDateTime);
        // If there aren't any broadcasts then show a sorry page, that is a 404
        // We don't want to clutter search results with loads of pages that says "sorry no results'
        if (!$broadcasts) {
            return $this->renderNoSchedule($viewData);
        }

        $viewData = array_merge($viewData, [
            'grouped_broadcasts' => $this->groupBroadcastsByPeriodOfDay($broadcasts, $startDateTime),
            'on_air_broadcast' => $this->getOnAirBroadcast($broadcasts),
        ]);

        return $this->renderWithChrome('schedules/by_day.html.twig', $viewData);
    }

    private function renderNoSchedule(array $viewData)
    {
        return $this->renderWithChrome(
            'schedules/no_schedule.html.twig',
            $viewData,
            new Response('', Response::HTTP_NOT_FOUND)
        );
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

    /**
     * Early - midnight until 6am
     * Morning - 6am until midday
     * Afternoon - midday until 6pm
     * Evening - 6pm until midnight
     * Late - midnight until 6am the next day
     *
     * @param Broadcast $broadcast
     * @param DateTimeImmutable $selectedDate
     * @return string
     */
    private function getBroadcastPeriodWord(Broadcast $broadcast, DateTimeImmutable $selectedDate): string
    {
        $selectedDayEnd = $selectedDate->setTime(23, 59, 59);

        $startBroadcast = $broadcast->getStartAt()->setTimezone(new DateTimeZone('Europe/London'));
        $startBroadcastHour = $startBroadcast->format('H');

        // Need to check for 'late' first as these broadcasts are actually the day after the selected date
        if ($startBroadcast > $selectedDayEnd) {
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

        return 'evening';
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

    /**
     * Radio schedules run midnight to 6AM
     * TV schedules run 6AM to 6AM
     * This method works out which times should be used for retrieving broadcasts.
     *
     * @param bool $serviceIsTv
     * @param null|string $date in Y-m-d format
     * @return DateTimeImmutable[] StartDate and EndDate
     */
    private function getStartAndEndTimes(bool $serviceIsTv, ?string $date): array
    {
        $tvOffsetHours = 6;
        if ($date) {
            // Try and create a date from the one provided
            $startDateTime = DateTimeImmutable::createFromFormat('Y-m-d|', $date, new DateTimeZone('Europe/London'));

            if (!$startDateTime) {
                throw $this->createNotFoundException('Invalid date');
            }
        } else {
            // Otherwise use now
            $dateTime = ApplicationTime::getLocalTime();

            // If a user is viewing the TV schedule between midnight and 6AM, we actually want to display yesterday's schedule.
            if ($serviceIsTv && (int) $dateTime->format('H') < $tvOffsetHours) {
                $dateTime = $dateTime->sub(new DateInterval('P1D'));
            }

            $startDateTime = $dateTime->setTime(0, 0, 0);
        }

        $interval = 'P1DT6H';

        // set day interval time
        if ($serviceIsTv) {
            $startDateTime = $startDateTime->setTime($tvOffsetHours, 0, 0);
            $interval = 'P1D';
        }

        $endDateTime = $startDateTime->add(new DateInterval($interval));

        return [$startDateTime, $endDateTime];
    }
}
