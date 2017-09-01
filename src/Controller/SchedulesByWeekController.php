<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Ds2013\Page\Schedules\ByWeekPage\SchedulesByWeekPagePresenter;
use App\ValueObject\BroadcastWeek;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Service\BroadcastsService;
use BBC\ProgrammesPagesService\Service\ServicesService;

class SchedulesByWeekController extends BaseController
{
    public function __invoke(Service $service, string $date, ServicesService $servicesService, BroadcastsService $broadcastService)
    {
        $this->setIstatsProgsPageType('schedules_week');
        $this->setContext($service);

        $broadcastWeek = new BroadcastWeek($date);

        $servicesInNetwork = $servicesService->findAllInNetworkActiveOn(
            $service->getNetwork()->getNid(),
            $broadcastWeek->end()
        );

        $broadcasts = [];

        if ($broadcastWeek->serviceIsActiveInThisPeriod($service)) {
            // Get broadcasts in relevant period
            $broadcasts = $broadcastService->findByServiceAndDateRange(
                $service->getSid(),
                $broadcastWeek->start(),
                $broadcastWeek->end(),
                BroadcastsService::NO_LIMIT
            );
        }

        $pagePresenter = new SchedulesByWeekPagePresenter(
            $service,
            $broadcastWeek->start(),
            $broadcasts,
            $date,
            $servicesInNetwork
        );

        $viewData = [
            'broadcast_week' => $broadcastWeek,
            'service' => $service,
            'number_of_services_in_network' => count($servicesInNetwork),
            'twin_service' => $this->twinService($service, $servicesInNetwork),
            'page_presenter' => $pagePresenter,
        ];

        // If the service is not active at all over the month, then the status code should be 404, so
        // that search engines do not index thousands of empty pages
        if (!$this->serviceIsActiveDuringWeek($service, $broadcastWeek)) {
            $this->response()->setStatusCode(404);
        }
        return $this->renderWithChrome('schedules/by_week.html.twig', $viewData);
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
}
