<?php
declare(strict_types = 1);

namespace App\Controller;

use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Chronos;

class SchedulesByMonthController extends BaseController
{
    public function __invoke(Service $service, string $date)
    {
        $this->setIstatsProgsPageType('schedules_month');
        $this->setContext($service);

        $firstOfMonth = Chronos::createFromFormat('Y-m|', $date, ApplicationTime::getLocalTimeZone())->firstOfMonth();
        $viewData = ['first_of_month' => $firstOfMonth, 'service' => $service];

        // If the service is not active at all over the month, then the status code should be 404, so
        // that search engines do not index thousands of empty pages
        if (!$this->serviceIsActiveDuringMonth($service, $firstOfMonth)) {
            $this->response()->setStatusCode(404);
        }
        return $this->renderWithChrome('schedules/by_month.html.twig', $viewData);
    }

    private function serviceIsActiveDuringMonth(Service $service, Chronos $firstOfMonth): bool
    {
        return (!$service->getStartDate() || $service->getStartDate() <= $firstOfMonth->endOfMonth()) && (!$service->getEndDate() || $firstOfMonth < $service->getEndDate());
    }
}
