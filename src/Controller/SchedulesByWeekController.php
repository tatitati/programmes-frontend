<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Controller\Traits\SchedulesPageResponseCodeTrait;
use App\Controller\Traits\UtcOffsetValidatorTrait;
use App\Ds2013\Presenters\Pages\Schedules\ByWeekPage\SchedulesByWeekPagePresenter;
use App\DsShared\Helpers\HelperFactory;
use App\ValueObject\BroadcastWeek;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\BroadcastGap;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Service\BroadcastsService;
use BBC\ProgrammesPagesService\Service\ServicesService;
use Cake\Chronos\Chronos;
use DateTimeZone;
use InvalidArgumentException;

class SchedulesByWeekController extends BaseController
{
    use UtcOffsetValidatorTrait;
    use SchedulesPageResponseCodeTrait;

    public function __invoke(
        Service $service,
        string $date,
        ServicesService $servicesService,
        BroadcastsService $broadcastService,
        HelperFactory $helperFactory
    ) {
        $utcOffset = $this->request()->query->get('utcoffset');
        if (!$this->isValidDate($date) || !$this->isValidUtcOffset($utcOffset)) {
            throw $this->createNotFoundException('Invalid date supplied');
        }

        $this->setIstatsProgsPageType('schedules_week');
        $this->setContext($service);

        if ($utcOffset) {
            ApplicationTime::setLocalTimeZone($utcOffset);
        } elseif ($service->isInternational()) {
            // "International" services are UTC, all others are Europe/London (the default)
            ApplicationTime::setLocalTimeZone('UTC');
        }

        try {
            $broadcastWeek = new BroadcastWeek($date);
        } catch (InvalidArgumentException $e) {
            throw $this->createNotFoundException('Invalid date');
        }

        $servicesInNetwork = $servicesService->findAllInNetworkActiveOn(
            $service->getNetwork()->getNid(),
            $broadcastWeek->end()
        );

        $daysOfBroadcasts = [];

        $broadcasts = [];
        if ($broadcastWeek->serviceIsActiveInThisPeriod($service)) {
            // Get broadcasts in relevant period
            $broadcasts = $broadcastService->findByServiceAndDateRange(
                $service->getSid(),
                $broadcastWeek->start(),
                $broadcastWeek->end(),
                BroadcastsService::NO_LIMIT
            );

            $daysOfBroadcasts = $this->groupBroadcasts($broadcasts);
            $daysOfBroadcasts = $this->addInBroadcastGaps($daysOfBroadcasts, $service);
        }

        $pagePresenter = new SchedulesByWeekPagePresenter(
            $service,
            $broadcastWeek->start(),
            $daysOfBroadcasts,
            $date,
            $servicesInNetwork
        );

        $viewData = [
            'broadcasts' => $daysOfBroadcasts,
            'broadcast_week' => $broadcastWeek,
            'service' => $service,
            'number_of_services_in_network' => count($servicesInNetwork),
            'twin_service' => $this->twinService($service, $servicesInNetwork),
            'page_presenter' => $pagePresenter,
            'schedule_reload' => $service->isInternational() && !$utcOffset,
            'localised_date_helper' => $helperFactory->getLocalisedDaysAndMonthsHelper(),
        ];

        $serviceIsActiveInThisPeriod = $this->serviceIsActiveDuringWeek($service, $broadcastWeek);

        // This is from a trait and sets a 404 status code or noindex on the controller
        // as appropriate when we have no broadcasts
        $this->setResponseCodeAndNoIndexProperties($serviceIsActiveInThisPeriod, $broadcasts, $broadcastWeek);

        return $this->renderWithChrome('schedules/by_week.html.twig', $viewData);
    }

    /**
     * @param Broadcast[][][] $daysOfBroadcasts
     * @param Service $service
     * @return Broadcast[][][]
     */
    private function addInBroadcastGaps(array $daysOfBroadcasts, Service $service): array
    {
        $tz = ApplicationTime::getLocalTimeZone();
        $newList = [];
        foreach ($daysOfBroadcasts as $date => $dayOfBroadcasts) {
            $newList[$date] = $this->createDayOfBroadcastsAndGaps($dayOfBroadcasts, $service, $tz, $date);
        }

        return $newList;
    }

    /**
     * @param Broadcast[][] $broadcastsToday
     * @param Service $service
     * @param DateTimeZone $tz
     * @param string $date in format Y-m-d
     * @return Broadcast[][]
     */
    private function addFinalBroadcastGapIfNecessary(array $broadcastsToday, Service $service, DateTimeZone $tz, string $date): array
    {

        $lastHour = end($broadcastsToday);
        $lastBroadcast = end($lastHour);
        $endOfDay = (new Chronos($date, $tz))->addDay()->startOfDay()->setTimezone(new DateTimeZone('UTC'));
        if ($lastBroadcast->getEndAt()->lt($endOfDay)) {
            $gap = new BroadcastGap($service, $lastBroadcast->getEndAt(), $endOfDay);
            $broadcastsToday[$lastBroadcast->getEndAt()->setTimezone($tz)->format('G')][] = $gap;
        }

        return $broadcastsToday;
    }

    /**
     * @param Broadcast[][] $dayOfBroadcasts
     * @param Service $service
     * @param DateTimeZone $tz
     * @param string $date in format Y-m-d
     * @return Broadcast[][]
     */
    private function createDayOfBroadcastsAndGaps(array $dayOfBroadcasts, Service $service, DateTimeZone $tz, string $date): array
    {
        $broadcastsToday = [];
        $priorBroadcast = null;
        foreach ($dayOfBroadcasts as $hour => $hourOfBroadcasts) {
            foreach ($hourOfBroadcasts as $broadcast) {
                // If there is space between the start of the current broadcast and
                // the end of the prior broadcast then inject a broadcast gap
                if ($priorBroadcast && $broadcast->getStartAt()->gt($priorBroadcast->getEndAt())) {
                    $broadcastsToday[$priorBroadcast->getEndAt()->setTimezone($tz)->format('G')][] = new BroadcastGap(
                        $service,
                        $priorBroadcast->getEndAt(),
                        $broadcast->getStartAt()
                    );
                }

                $broadcastsToday[$hour][] = $broadcast;
                $priorBroadcast = $broadcast;
            }
        }

        return $this->addFinalBroadcastGapIfNecessary($broadcastsToday, $service, $tz, $date);
    }

    /**
     * @param Broadcast[] $broadcasts
     * @return Broadcast[][][]
     */
    private function groupBroadcasts(array $broadcasts): array
    {
        $groupedBroadcasts = [];
        $tz = ApplicationTime::getLocalTimeZone();

        foreach ($broadcasts as $broadcast) {
            $start = $broadcast->getStartAt()->setTimezone($tz);
            $groupedBroadcasts[$start->format('Y/m/d')][$start->format('G')][] = $broadcast;
        }

        return $groupedBroadcasts;
    }

    private function serviceIsActiveDuringWeek(Service $service, BroadcastWeek $broadcastWeek): bool
    {
        return (!$service->getStartDate() || $service->getStartDate() <= $broadcastWeek->end()) && (!$service->getEndDate() || $broadcastWeek->start() < $service->getEndDate());
    }

    /**
     * @param Service $mainService
     * @param Service[] $servicesInNetwork
     * @return Service|null
     */
    private function twinService(Service $mainService, array $servicesInNetwork): ?Service
    {
        if (count($servicesInNetwork) !== 2) {
            return null;
        }

        // If there are two services, find the "other" service
        $otherServices = array_filter($servicesInNetwork, function (Service $sisterService) use ($mainService) {
            return ((string) $mainService->getSid() !== (string) $sisterService->getSid());
        });

        return reset($otherServices);
    }

    private function isValidDate($date): bool
    {
        // validate format
        if (!preg_match('#\d{4}/w\d{2}#', $date)) {
            return false;
        }

        // validate content
        list($year, $week) = explode('/', $date);
        $week = (int) str_replace('w', '', $week);

        if ($week < 1 || $week > 53 || $year < SchedulesByYearController::MINIMUM_VALID_YEAR) {
            return false;
        }

        return true;
    }
}
