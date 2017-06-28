<?php
declare(strict_types = 1);
namespace App\Controller;

use App\Ds2013\Page\Schedules\ByDayPage\SchedulesByDayPagePresenter;
use App\ValueObject\BroadcastDay;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\BroadcastsService;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use BBC\ProgrammesPagesService\Service\ServicesService;
use Cake\Chronos\Chronos;
use Symfony\Component\HttpFoundation\Response;

class SchedulesByDayController extends BaseController
{
    public function __invoke(
        Pid $pid,
        ?string $date,
        ServicesService $servicesService,
        BroadcastsService $broadcastService,
        CollapsedBroadcastsService $collapsedBroadcastsService
    ) {
        $this->setBrandingId('br-08368');

        $dateTimeToShow = $this->dateTimeToShow($date);
        if (!$dateTimeToShow) {
            throw $this->createNotFoundException('Invalid date');
        }

        $service = $servicesService->findByPidFull($pid);
        $this->setContext($service);

        if (!$service) {
            throw $this->createNotFoundException('Service not found');
        }

        $broadcastDay = new BroadcastDay($dateTimeToShow, $service->getNetwork()->getMedium());

        // Get all services that belong to this network
        $servicesInNetwork = $servicesService->findAllInNetworkActiveOn(
            $service->getNetwork()->getNid(),
            $broadcastDay->start()
        );

        $broadcasts = [];

        $liveCollapsedBroadcast = null;
        if ($service->isActiveAt($broadcastDay->start())) {
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
            $liveCollapsedBroadcast
        );

        $viewData = $this->viewData(
            $service,
            $broadcastDay->start(),
            $pagePresenter
        );

        // If there are no broadcasts, then the status code should be 404, so
        // that search engines do not index thousands of empty pages
        $response = new Response('', $broadcasts ? 200 : 404);
        return $this->renderWithChrome('schedules/by_day.html.twig', $viewData, $response);
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

    private function dateTimeToShow(?string $dateString): Chronos
    {
        if (!$dateString) {
            return Chronos::now('Europe/London');
        }

        // If a date has been provided, use the broadcast date for midday on
        // the given date
        return Chronos::createFromFormat('Y-m-d H:i:s', $dateString . '12:00:00', 'Europe/London');
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
}
