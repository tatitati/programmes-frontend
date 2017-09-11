<?php
declare(strict_types = 1);

namespace App\Controller;

use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SchedulesVanityRedirectController extends BaseController
{
    public function __invoke(Service $service, string $vanity, UrlGeneratorInterface $router)
    {
        $time = ApplicationTime::getLocalTime();

        if (in_array($vanity, ['today', 'tomorrow', 'yesterday'])) {
            if ($vanity === 'tomorrow') {
                $time = $time->tomorrow();
            } elseif ($vanity === 'yesterday') {
                $time = $time->yesterday();
            }

            return $this->redirectToRoute('schedules_by_day', ['pid' => $service->getPid(), 'date' => $time->format('Y/m/d')]);
        }

        if (in_array($vanity, ['last_week', 'next_week', 'this_week'])) {
            if ($vanity === 'next_week') {
                $time = $time->addWeek();
            } elseif ($vanity === 'last_week') {
                $time = $time->subWeek();
            }

            return $this->redirectToRoute('schedules_by_week', ['pid' => $service->getPid(), 'date' => $time->format('Y/\\wW')]);
        }

        return $this->createNotFoundException('Vanity URL ' . $vanity . ' not recognised');
    }
}
