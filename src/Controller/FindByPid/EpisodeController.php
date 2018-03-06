<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use App\Ds2013\Presenters\Section\Episode\Map\EpisodeMapPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use BBC\ProgrammesPagesService\Service\ContributionsService;
use BBC\ProgrammesPagesService\Service\ProgrammesAggregationService;
use BBC\ProgrammesPagesService\Service\PromotionsService;
use BBC\ProgrammesPagesService\Service\RelatedLinksService;

class EpisodeController extends BaseController
{
    public function __invoke(
        Episode $episode,
        ContributionsService $contributionsService,
        ProgrammesAggregationService $aggregationService,
        PromotionsService $promotionsService,
        RelatedLinksService $relatedLinksService,
        CollapsedBroadcastsService $collapsedBroadcastsService
    ) {
        $this->setIstatsProgsPageType('programmes_episode');
        $this->setContextAndPreloadBranding($episode);

        $clips = [];
        if ($episode->getAvailableClipsCount() > 0) {
            $clips = $aggregationService->findStreamableDescendantClips($episode, 4);
        }

        $contributions = [];
        if ($episode->getContributionsCount() > 0) {
            $contributions = $contributionsService->findByContributionToProgramme($episode);
        }

        $relatedLinks = [];
        if ($episode->getRelatedLinksCount() > 0) {
            $relatedLinks = $relatedLinksService->findByRelatedToProgramme($episode, ['related_site', 'miscellaneous']);
        }
        $upcomingBroadcasts = [];
        $lastOnBroadcasts = [];
        if ($episode->getFirstBroadcastDate()) {
            // Only search for broadcasts if a programme has them
            $upcomingBroadcasts = $collapsedBroadcastsService->findUpcomingByProgrammeWithFullServicesOfNetworksList($episode, 1);
            $lastOnBroadcasts = $collapsedBroadcastsService->findPastByProgramme($episode, 1);
        }

        // TODO check $episode->getPromotionsCount() once it is populated in
        // Faucet to potentially save on a DB query
        $promotions = $promotionsService->findActivePromotionsByEntityGroupedByType($episode);

        $episodeMapPresenter = new EpisodeMapPresenter(
            $episode,
            !empty($upcomingBroadcasts) ? reset($upcomingBroadcasts) : null,
            !empty($lastOnBroadcasts) ? reset($lastOnBroadcasts) : null
        );

        return $this->renderWithChrome('find_by_pid/episode.html.twig', [
            'contributions' => $contributions,
            'programme' => $episode,
            'clips' => $clips,
            'relatedLinks' => $relatedLinks,
            'promotions' => $promotions,
            'episodeMapPresenter' => $episodeMapPresenter,
        ]);
    }
}
