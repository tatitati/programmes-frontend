<?php
declare(strict_types = 1);
namespace App\Controller;

use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Service\ServicesService;
use Cake\Chronos\Chronos;

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
            'Historic' => [],
        ];

        $services = $servicesService->getAllInNetworks();

        $now = ApplicationTime::getLocalTime();
        $earliestBroadcastDate = new Chronos('1920-01-01T00:00:00Z');
        $latestBroadcastDate = $now->endOfDecade();

        $diff = $earliestBroadcastDate->diff($latestBroadcastDate);
        $pointsPerDay = 100 / $diff->days;

        $earliestDecade = $earliestBroadcastDate->startOfDecade()->year;
        $latestDecade = $latestBroadcastDate->startOfDecade()->year;

        $decades = range($earliestDecade, $latestDecade, 10);

        // https://jira.dev.bbc.co.uk/browse/PROGRAMMES-5792
        $blacklist = [
            'p00v5fbq', // BBC WORLD NEWS
            'p00qvyk4', // BBC Radio Events Stream 1
            'p00qvyjs', // BBC Radio Events Stream 2
            'p03yncmk', // BBC Persian TV
            'p05b88kr', // BBC Korean Radio
            'p05b8bdk', // BBC Amharic
            'p05b89mh', // BBC Omoro
            'p05b8b67', // BBC Tigrinya
            'p02yxxwj', // BBC World Service ANR
            'p02yxxfc', // BBC World Service Core
            'p02y9sgt', // BBC World Service News Internet
            'p02yxy62', // BBC World Service US Public Radio
            'p00hwfhp', // BBC WORLD NEWS Americas
            'p00fzlgd', // Radio 10
        ];

        foreach ($services as $service) {
            /** @var Service $service */
            // We only care about services with a startDate
            if (!$service->getStartDate()) {
                continue;
            }

            if (in_array((string) $service->getPid(), $blacklist)) {
                continue;
            }

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
                $now,
                $pointsPerDay
            );
        }

        // Remove any groups that have no networks within them
        foreach ($groups as $key => $networks) {
            if (!$networks) {
                unset($groups[$key]);
            }
        }

        return $this->renderWithChrome('schedules/home.html.twig', [
            'groups' => $groups,
            'decades' => $decades,
        ]);
    }

    private function createServiceItem(
        Service $service,
        Chronos $earliestBroadcastDate,
        Chronos $now,
        $pointsPerDay
    ): array {
        // We've already filtered out all services that don't have a startDate
        $diffFromStart = $earliestBroadcastDate->diff($service->getStartDate());
        $endDate = $service->getEndDate() ?? $now;
        $diffForLength = $service->getStartDate()->diff($endDate);

        return [
            'service' => $service,
            'isOngoing' => !$service->getEndDate(),
            'offset' => $diffFromStart->days * $pointsPerDay,
            'width' => $diffForLength->days * $pointsPerDay,
        ];
    }

    private function groupKeyForService(Service $service): string
    {
        $endDate = $service->getEndDate();
        if ($endDate && $endDate->isPast()) {
            return 'Historic';
        }

        $type = $service->getNetwork()->getType();
        if (in_array($type, ['TV', 'National Radio', 'Regional Radio', 'Local Radio'])) {
            return $type;
        }

        return 'Other';
    }
}
