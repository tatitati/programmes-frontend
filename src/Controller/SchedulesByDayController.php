<?php
declare(strict_types = 1);
namespace App\Controller;

use App\Ds2013\Page\Schedules\ByDayPage\SchedulesByDayPagePresenter;
use App\Ds2013\PresenterFactory;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\BroadcastsService;
use BBC\ProgrammesPagesService\Service\ServicesService;
use Cake\Chronos\Chronos;
use DateTimeZone;
use Symfony\Component\HttpFoundation\Response;

class SchedulesByDayController extends BaseController
{
    public function __invoke(Pid $pid, ?string $date, ServicesService $servicesService, BroadcastsService $broadcastService, PresenterFactory $presenterFactory)
    {
        $service = $servicesService->findByPidFull($pid);
        if (!$service) {
            throw $this->createNotFoundException('Service not found');
        }

        list($startDateTime, $endDateTime) = $this->getStartAndEndTimes($service->isTV(), $date);

        // Get all services that belong to this network
        $servicesInNetwork = $servicesService->findAllInNetworkActiveOn($service->getNetwork()->getNid(), $startDateTime);

        $broadcasts = [];

        if ($service->isActiveAt($startDateTime)) {
            // Get broadcasts in relevant period
            $broadcasts = $broadcastService->findByServiceAndDateRange($service->getSid(), $startDateTime, $endDateTime);
        }

        $pageViewModel = $presenterFactory->schedulesByDayPagePresenter(
            $service,
            $startDateTime,
            $endDateTime,
            $broadcasts,
            $servicesInNetwork
        );

        // If the service is not active render a 404
        if (!$broadcasts) {
            return $this->renderWithChrome(
                'schedules/no_schedule.html.twig',
                ['page_presenter' => $pageViewModel],
                new Response('', Response::HTTP_NOT_FOUND)
            );
        }

        return $this->renderWithChrome('schedules/by_day.html.twig', ['page_presenter' => $pageViewModel]);
    }

    /**
     * Radio schedules run midnight to 6AM
     * TV schedules run 6AM to 6AM
     * This method works out which times should be used for retrieving broadcasts.
     *
     * @param bool $serviceIsTv
     * @param null|string $date in Y-m-d format
     * @return Chronos[] StartDate and EndDate
     */
    private function getStartAndEndTimes(bool $serviceIsTv, ?string $date): array
    {
        $tvOffsetHours = 6;
        if ($date) {
            // Try and create a date from the one provided
            $startDateTime = Chronos::createFromFormat('Y-m-d|', $date, 'Europe/London');

            if (!$startDateTime) {
                throw $this->createNotFoundException('Invalid date');
            }
        } else {
            // Otherwise use now
            $startDateTime = Chronos::today('Europe/London');

            // If a user is viewing the TV schedule between midnight and 6AM, we actually want to display yesterday's schedule.
            if ($serviceIsTv && $startDateTime->wasWithinLast($tvOffsetHours . ' hours')) {
                $startDateTime = Chronos::yesterday('Europe/London');
            }
        }

        $scheduleHours = '30';

        // set day interval time
        if ($serviceIsTv) {
            $startDateTime = $startDateTime->addHours($tvOffsetHours);
            $scheduleHours = '24';
        }

        $endDateTime = $startDateTime->addHours($scheduleHours);

        return [$startDateTime, $endDateTime];
    }
}
