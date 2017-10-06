<?php
declare(strict_types = 1);
namespace App\Controller;

use App\DsShared\Helpers\HelperFactory;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Service\BroadcastsService;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use BBC\ProgrammesPagesService\Service\NetworksService;
use Cake\Chronos\Chronos;
use Symfony\Component\HttpFoundation\Request;

class SchedulesOnNowController extends BaseController
{
    public function __invoke(
        BroadcastsService $broadcastsService,
        CollapsedBroadcastsService $collapsedBroadcastsService,
        NetworksService $networksService,
        HelperFactory $helperFactory,
        Request $request,
        string $networkUrlKey
    ) {
        $network = $networksService->findByUrlKeyWithDefaultService($networkUrlKey);

        if (!$network || !$network->getDefaultService()) {
            throw $this->createNotFoundException('No network or service found from network key ' . $networkUrlKey);
        }

        $this->setTimeZone($network);

        $broadcast = $broadcastsService->findOnNowByService($network->getDefaultService());
        if (!$broadcast) {
            throw $this->createNotFoundException('No broadcasts found.');
        }

        $designSystem = $request->query->get('partial');
        if (!in_array($designSystem, ['legacy_2013', 'legacy_amen'])) {
            return $this->redirectToRoute('find_by_pid', ['pid' => (string) $broadcast->getProgrammeItem()->getPid()]);
        }

        $collapsedBroadcast = $collapsedBroadcastsService->findByBroadcast($broadcast);
        $simulcastUrl = $helperFactory->getLiveBroadcastHelper()->simulcastUrl($collapsedBroadcast);

        $cacheLifetime = $this->calculateCacheLifetime($broadcast->getEndAt());
        $this->response()->setPublic()->setMaxAge($cacheLifetime);

        return $this->render('schedules/on_now_' . $designSystem . '.html.twig', [
            'broadcast' => $broadcast,
            'simulcastUrl' => $simulcastUrl,
            'isRadio' => $network->isRadio(),
        ]);
    }

    private function setTimeZone(Network $network)
    {
        if ($network->isInternational()) {
            ApplicationTime::setLocalTimeZone('UTC');
        }
    }

    private function calculateCacheLifetime(Chronos $broadcastEndAt): int
    {
        $secondsUntilBroadcastEndAt = $broadcastEndAt->diffInSeconds(ApplicationTime::getLocalTime());

        // Cache for 10 minutes, unless the broadcast finishes sooner
        // Minimum cache life of 15 seconds
        if ($secondsUntilBroadcastEndAt < 15) {
            return 15;
        }

        if ($secondsUntilBroadcastEndAt > 600) {
            return 600;
        }

        return $secondsUntilBroadcastEndAt;
    }
}
