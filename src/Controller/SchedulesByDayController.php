<?php
declare(strict_types = 1);
namespace App\Controller;

use App\Ds2013\Page\Schedules\ByDayPage\SchedulesByDayPagePresenter;
use App\DsShared\Helpers\HelperFactory;
use App\ValueObject\BroadcastDay;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Service\BroadcastsService;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use BBC\ProgrammesPagesService\Service\NetworksService;
use BBC\ProgrammesPagesService\Service\ServicesService;
use Cake\Chronos\Chronos;
use Cake\Chronos\Date;

class SchedulesByDayController extends BaseController
{
    /** @var HelperFactory */
    protected $helperFactory;

    public function __invoke(
        Service $service,
        ?string $date,
        NetworksService $networksService,
        ServicesService $servicesService,
        BroadcastsService $broadcastService,
        CollapsedBroadcastsService $collapsedBroadcastsService,
        HelperFactory $helperFactory
    ) {
        if (!$this->isValidDate($date)) {
            throw $this->createNotFoundException('Invalid date supplied');
        }

        $this->helperFactory = $helperFactory;
        $this->setIstatsProgsPageType('schedules_day');
        $this->setContext($service);

        $dateTimeToShow = $this->dateTimeToShow($date, $service);
        if (!$dateTimeToShow) {
            throw $this->createNotFoundException('Invalid date');
        }

        $broadcastDay = new BroadcastDay($dateTimeToShow, $service->getNetwork()->getMedium());

        // Get all services that belong to this network
        $servicesInNetwork = $servicesService->findAllInNetworkActiveOn(
            $service->getNetwork()->getNid(),
            $broadcastDay->start()
        );

        $broadcasts = [];

        $liveCollapsedBroadcast = null;
        if ($broadcastDay->serviceIsActiveInThisPeriod($service)) {
            // Get broadcasts in relevant period
            $broadcasts = $broadcastService->findByServiceAndDateRange(
                $service->getSid(),
                $broadcastDay->start(),
                $broadcastDay->end()
            );

            // Hydrate any live broadcasts with a collapsed broadcast
            if ($broadcastDay->isNow()) {
                $liveCollapsedBroadcast = $this->findLiveCollapsedBroadcast(
                    $broadcasts,
                    $collapsedBroadcastsService
                );
            }
        }

        $pagePresenter = new SchedulesByDayPagePresenter(
            $service,
            $broadcastDay->start(),
            $broadcasts,
            $date,
            $servicesInNetwork,
            $this->getOtherNetworks($service, $networksService, $broadcastDay),
            $liveCollapsedBroadcast,
            $this->helperFactory->getLocalisedDaysAndMonthsHelper()
        );

        $viewData = $this->viewData(
            $service,
            $broadcastDay->start(),
            $pagePresenter,
            $service->isInternational() && !$this->request()->query->has('utcoffset')
        );

        // If there are no broadcasts, then the status code should be 404, so
        // that search engines do not index thousands of empty pages
        if (!$broadcasts) {
            $this->response()->setStatusCode(404);
        }

        $this->setIstatsExtraLabels($this->getIstatsExtraLabels($date, $broadcastDay->start()->isYesterday()));

        return $this->renderWithChrome('schedules/by_day.html.twig', $viewData);
    }

    private function getIstatsExtraLabels(?string $date, bool $broadcastStartedYesterday): array
    {
        if (isset($date)) {
            $tz = ApplicationTime::getLocalTimeZone();
            $urlDate = Date::createFromFormat('Y/m/d', $date, $tz);
            $diffInDays = Date::now($tz)->diffInDays($urlDate, false);
            return [
                'schedule_offset' => $this->getScheduleOffset($diffInDays),
                'schedule_context' => $this->getScheduleContext($diffInDays),
                'schedule_current_fortnight' => $this->getScheduleCurrentFortnight($diffInDays),
            ];
        }

        // for TV if there is no date in the URL and is before 6:00 we are displaying yesterday schedule page.
        // Send same iStats values as in v2 so it will be track as yesterday instead of today broadcast
        // even when we know the today broadcast hasn't started yet
        if ($broadcastStartedYesterday) {
            return [
                'schedule_offset' => '-1',
                'schedule_context' => 'past',
                'schedule_current_fortnight' => 'true',
            ];
        }

        return [
            'schedule_offset' => '0',
            'schedule_context' => 'today',
            'schedule_current_fortnight' => 'true',
        ];
    }

    /**
     * Returns a string representation of days between the current schedules page and today.
     * Examples returned: "-5", "0", "+3"
     */
    private function getScheduleOffset(int $diffInDays): string
    {
        if ($diffInDays == 0) {
            return (string) $diffInDays;
        }

        return sprintf("%+d", $diffInDays);
    }

    private function getScheduleContext(int $diffInDays): string
    {
        if ($diffInDays == 0) {
            return 'today';
        } elseif ($diffInDays < 0) {
            return 'past';
        } else {
            return 'future';
        }
    }

    private function getScheduleCurrentFortnight(int $diffInDays): string
    {
        return (abs($diffInDays) > 7) ? 'false' : 'true';
    }

    private function viewData(
        Service $service,
        Chronos $broadcastDayStart,
        SchedulesByDayPagePresenter $pagePresenter,
        bool $scheduleReload
    ): array {
        return [
            'service' => $service,
            'broadcast_day_start' => $broadcastDayStart,
            'page_presenter' => $pagePresenter,
            'schedule_reload' => $scheduleReload,
        ];
    }

    private function dateTimeToShow(?string $dateString, Service $service): Chronos
    {
        if ($this->request()->query->has('utcoffset')) {
            ApplicationTime::setLocalTimeZone($this->request()->query->get('utcoffset'));
        } elseif ($service->isInternational()) {
            // "International" services are UTC, all others are Europe/London (the default)
            ApplicationTime::setLocalTimeZone('UTC');
        }
        if (!$dateString) {
            return ApplicationTime::getLocalTime();
        }
        // Routing should ensure $dateString is in format \d{4}-\d{2}-\d{2}
        // If a date has been provided, use the broadcast date for midday on
        // the given date
        return Chronos::createFromFormat('Y/m/d H:i:s', $dateString . ' 12:00:00', ApplicationTime::getLocalTimeZone());
    }

    private function findLiveCollapsedBroadcast(
        array $broadcasts,
        CollapsedBroadcastsService $collapsedBroadcastsService
    ): ?CollapsedBroadcast {
        foreach ($broadcasts as $broadcast) {
            if ($broadcast->isOnAir()) {
                return $collapsedBroadcastsService->findByBroadcast($broadcast);
            }
        }

        return null;
    }

    /**
     * @param Service $service
     * @param NetworksService $networksService
     * @param BroadcastDay $broadcastDay
     * @return Network[]
     */
    private function getOtherNetworks(Service $service, NetworksService $networksService, BroadcastDay $broadcastDay): array
    {
        if (!$service->isTv() || $service->isInternational()) {
            return [];
        }

        $allTvNetworks = $networksService->findPublishedNetworksByType(
            ['TV'],
            NetworksService::NO_LIMIT
        );
        $whitelistedNetworks = [
            'bbc_one',
            'bbc_two',
            'bbc_three',
            'bbc_four',
            'cbbc',
            'cbeebies',
            'bbc_news24',
            'bbc_parliament',
            'bbc_alba',
            's4cpbs',
        ];
        return array_filter($allTvNetworks, function (Network $network) use ($broadcastDay, $whitelistedNetworks) {
            return in_array((string) $network->getNid(), $whitelistedNetworks) &&
                $broadcastDay->serviceIsActiveInThisPeriod($network->getDefaultService());
        });
    }

    private function isValidDate(?string $date): bool
    {
        if (is_null($date)) {
            return true;
        }

        if (!preg_match('#\d{4}/\d{2}/\d{2}#', $date)) {
            return false;
        }

        list($year, $month, $day) = explode('/', $date);
        return checkdate((int) $month, (int) $day, (int) $year);
    }
}
