<?php
declare(strict_types = 1);
namespace App\Controller;

use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Service\ServicesService;
use DateTimeImmutable;

class SchedulesHomeController extends BaseController
{
    public function __invoke(ServicesService $servicesService)
    {
        $this->setBrandingId('br-08368');

        $groups = [
            'TV' => [],
            'National Radio' => [],
            'Regional Radio' => [],
            'Local Radio' => [],
            'Other' => [],
            'Historical' => [],
        ];

        $services = $servicesService->getAllInNetworks();

        $now = ApplicationTime::getTime();
        $earliestBroadcastDate = new DateTimeImmutable('1920-01-01T00:00:00Z');
        $latestBroadcastDate = $this->getFinalMomentInCurrentDecade();

        $diff = $earliestBroadcastDate->diff($latestBroadcastDate);
        $pointsPerDay = 100 / $diff->days;

        $earliestDecade = $this->getDecade($earliestBroadcastDate);
        $latestDecade = $this->getDecade($latestBroadcastDate);

        $decades = range($earliestDecade, $latestDecade, 10);
        $decadePercent = 100 / count($decades);

        foreach ($services as $service) {
            /** @var Service $service */
            $network = $service->getNetwork();
            $groupKey = $this->groupKeyForService($service);

            $nid = (string) $network->getNid();
            if (!isset($groups[$groupKey][$nid])) {
                $groups[$groupKey][$nid] = [
                    'network' => $network,
                    'services' => [],
                ];
            }

            $groups[$groupKey][$nid]['services'][] = $this->createServiceItem(
                $service,
                $earliestBroadcastDate,
                $latestBroadcastDate,
                $now,
                $pointsPerDay
            );
        }

        return $this->renderWithChrome('schedules/home.html.twig', [
            'groups' => $groups,
            'decades' => $decades,
            'decadePercent' => $decadePercent,
        ]);
    }

    private function createServiceItem(
        Service $service,
        DateTimeImmutable $earliestBroadcastDate,
        DateTimeImmutable $latestBroadcastDate,
        DateTimeImmutable $now,
        $pointsPerDay
    ): array {
        $result = [
            'service' => $service,
            'hasDate' => false,
            'isOngoing' => false,
            'offset' => null,
            'width' => null,
        ];

        if (!$service->getStartDate()) {
            return $result;
        }

        $result['hasDate'] = true;
        $result['isOngoing'] = !$service->getEndDate();
        $diffFromStart = $earliestBroadcastDate->diff($service->getStartDate());
        $endDate = $service->getEndDate() ?? $now;
        $diffForLength = $service->getStartDate()->diff($endDate);
        $result['offset'] = $diffFromStart->days * $pointsPerDay;
        $result['width'] = $diffForLength->days * $pointsPerDay;

        return $result;
    }

    private function groupKeyForService(Service $service): string
    {
        if ($service->getEndDate()) {
            return 'Historical';
        }

        $type = $service->getNetwork()->getType();
        if (in_array($type, ['TV', 'National Radio', 'Regional Radio', 'Local Radio'])) {
            return $type;
        }

        return 'Other';
    }

    private function getDecade(DateTimeImmutable $datetime): float
    {
        $year = (int) $datetime->format('Y');
        return intdiv($year, 10) * 10;
    }

    private function getFinalMomentInCurrentDecade(): DateTimeImmutable
    {
        $decade = $this->getDecade(ApplicationTime::getTime()) + 9;
        return new DateTimeImmutable($decade . '-12-31 23:59:59Z');
    }
}
