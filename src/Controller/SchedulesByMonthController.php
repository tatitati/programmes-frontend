<?php
declare(strict_types = 1);

namespace App\Controller;

use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\ServicesService;
use Cake\Chronos\Date;

class SchedulesByMonthController extends BaseController
{
    public function __invoke(Pid $pid, string $date, ServicesService $servicesService)
    {
        $service = $servicesService->findByPidFull($pid);
        if (!$service) {
            throw $this->createNotFoundException('Service not found');
        }

        $this->setContext($service);

        $firstOfMonth = Date::createFromFormat('Y-m', $date, ApplicationTime::getLocalTimeZone())->firstOfMonth();
        $viewData = ['first_of_month' => $firstOfMonth, 'service' => $service];

        // If the service is not active at all over the month, then the status code should be 404, so
        // that search engines do not index thousands of empty pages
        if (!$this->serviceIsActiveDuringYear($service, $firstOfMonth)) {
            $this->response()->setStatusCode(404);
        }
        return $this->renderWithChrome('schedules/by_month.html.twig', $viewData);
    }

    private function serviceIsActiveDuringYear(Service $service, Date $firstOfMonth): bool
    {
        return (!$service->getStartDate() || $service->getStartDate() <= $firstOfMonth->endOfMonth()) && (!$service->getEndDate() || $firstOfMonth < $service->getEndDate());
    }
}
