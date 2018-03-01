<?php
declare(strict_types = 1);
namespace App\Controller\ProgrammeEpisodes;

use App\Ds2013\PresenterFactory;
use App\Ds2013\Presenters\Utilities\Paginator\PaginatorPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use Symfony\Component\HttpFoundation\Request;

class GuideController extends BaseProgrammeEpisodesController
{
    const LIMIT = 30;

    public function __invoke(
        ProgrammeContainer $programme,
        PresenterFactory $presenterFactory,
        ProgrammesService $programmesService,
        CollapsedBroadcastsService $collapsedBroadcastService,
        Request $request
    ) {
        $this->setContextAndPreloadBranding($programme);
        $this->setInternationalStatusAndTimezoneFromContext($programme);
        $this->setIstatsProgsPageType('episodes_guide');
        $page = $this->getPage();

        $children = $programmesService->findEpisodeGuideChildren(
            $programme,
            self::LIMIT,
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

            if ($totalChildrenCount > self::LIMIT) {
                $paginator = new PaginatorPresenter($page, self::LIMIT, $totalChildrenCount);
            }
        }

        $subNavPresenter = $this->getSubNavPresenter($collapsedBroadcastService, $programme, $presenterFactory);
        $upcomingBroadcasts = $this->getUpcomingBroadcastsIndexedByProgrammePid($programme, $collapsedBroadcastService);

        return $this->renderWithChrome('programme_episodes/guide.html.twig', [
            'programme' => $programme,
            'children' => $children,
            'paginatorPresenter' => $paginator,
            'subNavPresenter' => $subNavPresenter,
            'upcomingBroadcasts' => $upcomingBroadcasts,
        ]);
    }

    protected function getUpcomingBroadcastsIndexedByProgrammePid(
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
