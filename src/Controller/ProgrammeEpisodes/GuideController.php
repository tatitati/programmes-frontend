<?php
declare(strict_types = 1);
namespace App\Controller\ProgrammeEpisodes;

use App\Controller\BaseController;
use App\Ds2013\PresenterFactory;
use App\Ds2013\Presenters\Utilities\Paginator\PaginatorPresenter;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use BBC\ProgrammesPagesService\Service\ProgrammesAggregationService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;

class GuideController extends BaseController
{
    public function __invoke(
        ProgrammeContainer $programme,
        PresenterFactory $presenterFactory,
        string $extension,
        ProgrammesService $programmesService,
        ProgrammesAggregationService $programmeAggregationService,
        CollapsedBroadcastsService $collapsedBroadcastService
    ) {
        $this->setContextAndPreloadBranding($programme);
        $this->setInternationalStatusAndTimezoneFromContext($programme);
        $this->setIstatsProgsPageType('episodes_guide');
        $page = $this->getPage();
        $limit = 30;

        $children = $programmesService->findEpisodeGuideChildren(
            $programme,
            $limit,
            $page
        );

        // If you visit an out-of-bounds page then throw a 404. Page one should
        // always be a 200 so search engines don't drop their reference to the
        // page if a programme has no episodes
        if (!$children && $page !== 1) {
            throw $this->createNotFoundException('Page does not exist');
        }

        $paginator = null;

        if ($children) {
            $totalChildrenCount = $programmesService->countEpisodeGuideChildren($programme);

            if ($totalChildrenCount > $limit) {
                $paginator = new PaginatorPresenter($page, $limit, $totalChildrenCount);
            }
        }

        $upcomingBroadcastCount = $collapsedBroadcastService->countUpcomingByProgramme($programme, CacheInterface::MEDIUM);
        $totalAvailableEpisodes = $programmeAggregationService->countStreamableOnDemandEpisodes($programme);

        $subNavPresenter = $presenterFactory->episodesSubNavPresenter(
            $this->request()->attributes->get('_route'),
            $programme->getNetwork() === null || !$programme->getNetwork()->isInternational(),
            $programme->getFirstBroadcastDate() !== null,
            $totalAvailableEpisodes,
            $programme->getPid(),
            $upcomingBroadcastCount
        );
        $upcomingBroadcasts = $this->getUpcomingBroadcastsIndexedByProgrammePid($programme, $collapsedBroadcastService);

        $this->setIstatsExtraLabels(
            [
                'has_available_items' => count($children) > 0 ? 'true' : 'false',
                'total_available_episodes' => isset($totalChildrenCount) ? (string) $totalChildrenCount : '0',
            ]
        );

        return $this->renderWithChrome('programme_episodes/guide' . $extension . '.html.twig', [
            'programme' => $programme,
            'children' => $children,
            'paginatorPresenter' => $paginator,
            'subNavPresenter' => $subNavPresenter,
            'upcomingBroadcasts' => $upcomingBroadcasts,
        ]);
    }

    public function getUpcomingBroadcastsIndexedByProgrammePid(
        ProgrammeContainer $programme,
        CollapsedBroadcastsService $collapsedBroadcastsService
    ): array {
        $broadcasts = $collapsedBroadcastsService->findUpcomingByProgrammeWithFullServicesOfNetworksList($programme);
        $upcoming = [];

        foreach ($broadcasts as $broadcast) {
            $upcoming[(string) $broadcast->getProgrammeItem()->getPid()] = $broadcast;
        }

        return $upcoming;
    }
}
