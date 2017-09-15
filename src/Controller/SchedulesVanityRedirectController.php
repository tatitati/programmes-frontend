<?php
declare(strict_types = 1);

namespace App\Controller;

use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SchedulesVanityRedirectController extends BaseController
{
    public function __invoke(Request $request, string $pid, string $vanity, UrlGeneratorInterface $router)
    {
        $time = ApplicationTime::getLocalTime();

        $params = $request->query->all();
        $params['pid'] = $pid;

        if (in_array($vanity, ['today', 'tomorrow', 'yesterday'])) {
            if ($vanity === 'tomorrow') {
                $time = $time->tomorrow();
            } elseif ($vanity === 'yesterday') {
                $time = $time->yesterday();
            }

            $params['date'] = $time->format('Y/m/d');
            return $this->redirectToRoute('schedules_by_day', $params);
        }

        if (in_array($vanity, ['last_week', 'next_week', 'this_week'])) {
            if ($vanity === 'next_week') {
                $time = $time->addWeek();
            } elseif ($vanity === 'last_week') {
                $time = $time->subWeek();
            }

            $params['date'] = $time->format('Y/\\wW');
            return $this->redirectToRoute('schedules_by_week', $params);
        }

        if (in_array($vanity, ['last_month', 'next_month', 'this_month'])) {
            if ($vanity === 'next_month') {
                $time = $time->addMonth();
            } elseif ($vanity === 'last_month') {
                $time = $time->subMonth();
            }

            $params['date'] = $time->format('Y/m');
            return $this->redirectToRoute('schedules_by_month', $params);
        }

        throw $this->createNotFoundException('Vanity URL ' . $vanity . ' not recognised');
    }
}
