<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use App\Controller\Helpers\StructuredDataHelper;
use App\Ds2013\PresenterFactory;
use App\DsShared\Helpers\CanonicalVersionHelper;
use App\ExternalApi\Electron\Service\ElectronService;
use App\ExternalApi\FavouritesButton\Service\FavouritesButtonService;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use BBC\ProgrammesPagesService\Service\ContributionsService;
use BBC\ProgrammesPagesService\Service\GroupsService;
use BBC\ProgrammesPagesService\Service\ProgrammesAggregationService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use BBC\ProgrammesPagesService\Service\PromotionsService;
use BBC\ProgrammesPagesService\Service\RelatedLinksService;
use BBC\ProgrammesPagesService\Service\SegmentEventsService;
use BBC\ProgrammesPagesService\Service\VersionsService;

class EpisodeController extends BaseController
{
    public function __invoke(
        Episode $episode,
        ContributionsService $contributionsService,
        ProgrammesService $programmesService,
        ProgrammesAggregationService $aggregationService,
        PromotionsService $promotionsService,
        RelatedLinksService $relatedLinksService,
        CollapsedBroadcastsService $collapsedBroadcastsService,
        FavouritesButtonService $favouritesButtonService,
        VersionsService $versionsService,
        SegmentEventsService $segmentEventsService,
        ElectronService $electronService,
        GroupsService $groupsService,
        PresenterFactory $presenterFactory,
        CanonicalVersionHelper $canonicalVersionHelper,
        StructuredDataHelper $structuredDataHelper
    ) {
        $this->setIstatsProgsPageType('programmes_episode');
        $this->setContextAndPreloadBranding($episode);

        // TODO: After PROGRAMMES-6284 is done, we can just fetch the versions we actually need and this can go
        $versions = $versionsService->findByProgrammeItem($episode);
        $availableVersions = $this->getAvailableVersions($versions);

        $clips = [];
        if ($episode->getAvailableClipsCount() > 0) {
            $clips = $aggregationService->findStreamableDescendantClips($episode, 4);
        }

        $galleries = [];
        if ($episode->getAggregatedGalleriesCount() > 0) {
            $galleries = $aggregationService->findDescendantGalleries($episode, 4);
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
        $allBroadcasts = [];
        if ($episode->getFirstBroadcastDate()) {
            // Only search for broadcasts if a programme has them
            $upcomingBroadcasts = $collapsedBroadcastsService->findUpcomingByProgrammeWithFullServicesOfNetworksList($episode, 1);
            $lastOnBroadcasts = $collapsedBroadcastsService->findPastByProgrammeWithFullServicesOfNetworksList($episode, 1);
            $allBroadcasts = $collapsedBroadcastsService->findByProgrammeWithFullServicesOfNetworksList($episode, 100);
        }

        $featuredIn = $groupsService->findByCoreEntityMembership($episode, 'Collection');

        // TODO check $episode->getPromotionsCount() once it is populated in
        // Faucet to potentially save on a DB query
        $promotions = $promotionsService->findActivePromotionsByEntityGroupedByType($episode);

        /** @var Episode|null $nextEpisode */
        $nextEpisode = null;
        /** @var Episode|null $previousEpisode */
        $previousEpisode = null;

        $upcomingBroadcast = !empty($upcomingBroadcasts) ? reset($upcomingBroadcasts) : null;
        $lastOnBroadcast =  !empty($lastOnBroadcasts) ? reset($lastOnBroadcasts) : null;

        if (!$episode->isTleo()) {
            $nextEpisode = $programmesService->findNextSiblingByProgramme($episode);
            $previousEpisode = $programmesService->findPreviousSiblingByProgramme($episode);
        }

        $episodeMapPresenter = $presenterFactory->episodeMapPresenter(
            $episode,
            $availableVersions,
            $upcomingBroadcast,
            $lastOnBroadcast,
            $nextEpisode,
            $previousEpisode
        );

        $segmentsListPresenter = null;
        if ($versions) {
            $canonicalVersion = $canonicalVersionHelper->getCanonicalVersion($versions);
            if ($canonicalVersion->getSegmentEventCount()) {
                $segmentEvents = $segmentEventsService->findByVersionWithContributions($canonicalVersion);
                if ($segmentEvents) {
                    $segmentsListPresenter = $presenterFactory->segmentsListPresenter(
                        $episode,
                        $segmentEvents,
                        $upcomingBroadcast,
                        $lastOnBroadcast
                    );
                }
            }
        }

        $supportingContentItemsPromise = $electronService->fetchSupportingContentItemsForProgramme($episode);

        $resolvedPromises = $this->resolvePromises(['favouritesButton' => $favouritesButtonService->getContent(), 'supportingContentItems' => $supportingContentItemsPromise]);

        $schema = $this->getSchema($structuredDataHelper, $episode, $upcomingBroadcast, $clips, $contributions);

        return $this->renderWithChrome('find_by_pid/episode.html.twig', [
            'schema' => $schema,
            'contributions' => $contributions,
            'programme' => $episode,
            'clips' => $clips,
            'galleries' => $galleries,
            'relatedLinks' => $relatedLinks,
            'featuredIn' => $featuredIn,
            'promotions' => $promotions,
            'allBroadcasts' => $allBroadcasts,
            'episodeMapPresenter' => $episodeMapPresenter,
            'segmentsListPresenter' => $segmentsListPresenter,
            'favouritesButton' => $resolvedPromises['favouritesButton'],
            'supportingContentItems' => $resolvedPromises['supportingContentItems'],
        ]);
    }

    private function getAvailableVersions(array $versions): array
    {
        return array_filter($versions, function (Version $version) {
            return $version->isDownloadable() || $version->isStreamable();
        });
    }

    private function getSchema(
        StructuredDataHelper $structuredDataHelper,
        Episode $episode,
        ?CollapsedBroadcast $upcomingBroadcast,
        array $clips,
        array $contributions
    ): array {
        $schemaContext = $structuredDataHelper->getSchemaForEpisode($episode, true);
        if ($upcomingBroadcast) {
            if ($episode->isStreamable()) {
                $schemaContext['publication'] = [
                    $structuredDataHelper->getSchemaForOnDemand($episode),
                    $structuredDataHelper->getSchemaForCollapsedBroadcast($upcomingBroadcast),
                ];
            } else {
                $schemaContext['publication'] = $structuredDataHelper->getSchemaForCollapsedBroadcast($upcomingBroadcast);
            }
        } elseif ($episode->isStreamable()) {
            $schemaContext['publication'] = $structuredDataHelper->getSchemaForOnDemand($episode);
        }

        foreach ($clips as $clip) {
            $schemaContext['hasPart'][] = $structuredDataHelper->buildSchemaForClip($clip);
        }

        $actors = [];
        $contributors = [];
        /** @var Contribution $contribution */
        foreach ($contributions as $contribution) {
            if ($contribution->getCharacterName()) {
                $actors[] = $structuredDataHelper->getSchemaForActorContribution($contribution);
            } else {
                $contributors[] = $structuredDataHelper->getSchemaForNonActorContribution($contribution);
            }
        }

        if ($actors) {
            $schemaContext['actor'] = $actors;
        }
        if ($contributors) {
            $schemaContext['contributor'] = $contributors;
        }

        return $structuredDataHelper->prepare($schemaContext);
    }
}
