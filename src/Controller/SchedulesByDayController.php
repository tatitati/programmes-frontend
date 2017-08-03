<?php
declare(strict_types = 1);
namespace App\Controller;

use App\Ds2013\Page\Schedules\ByDayPage\SchedulesByDayPagePresenter;
use App\ValueObject\BroadcastDay;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\BroadcastsService;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use BBC\ProgrammesPagesService\Service\NetworksService;
use BBC\ProgrammesPagesService\Service\ServicesService;
use Cake\Chronos\Chronos;

class SchedulesByDayController extends BaseController
{
    public function __invoke(
        Service $service,
        ?string $date,
        NetworksService $networksService,
        ServicesService $servicesService,
        BroadcastsService $broadcastService,
        CollapsedBroadcastsService $collapsedBroadcastsService
    ) {
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
        if ($broadcastDay->serviceIsActiveOnThisDay($service)) {
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
            $liveCollapsedBroadcast
        );

        $viewData = $this->viewData(
            $service,
            $broadcastDay->start(),
            $pagePresenter
        );

        // If there are no broadcasts, then the status code should be 404, so
        // that search engines do not index thousands of empty pages
        if (!$broadcasts) {
            $this->response()->setStatusCode(404);
        }
        return $this->renderWithChrome('schedules/by_day.html.twig', $viewData);
    }

    private function viewData(
        Service $service,
        Chronos $broadcastDayStart,
        SchedulesByDayPagePresenter $pagePresenter
    ): array {
        return [
            'service' => $service,
            'broadcast_day_start' => $broadcastDayStart,
            'page_presenter' => $pagePresenter,
        ];
    }

    private function dateTimeToShow(?string $dateString, Service $service): Chronos
    {
        // "International" services are UTC, all others are Europe/London (the default)
        if ($service->isInternational()) {
            ApplicationTime::setLocalTimeZone('UTC');
        }
        if (!$dateString) {
            return ApplicationTime::getLocalTime();
        }
        // Routing should ensure $dateString is in format \d{4}-\d{2}-\d{2}
        // If a date has been provided, use the broadcast date for midday on
        // the given date
        return Chronos::createFromFormat('Y-m-d H:i:s', $dateString . ' 12:00:00', ApplicationTime::getLocalTimeZone());
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
                $broadcastDay->serviceIsActiveOnThisDay($network->getDefaultService());
        });
    }
}
