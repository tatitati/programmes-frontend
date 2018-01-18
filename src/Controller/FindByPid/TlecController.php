<?php
declare(strict_types = 1);

namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use App\DsAmen\PresenterFactory;
use App\DsShared\Helpers\HelperFactory;
use App\ExternalApi\Ada\Service\AdaClassService;
use App\ExternalApi\Electron\Service\ElectronService;
use App\ExternalApi\FavouritesButton\Service\FavouritesButtonService;
use App\ExternalApi\RecEng\Service\RecEngService;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Promotion;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use BBC\ProgrammesPagesService\Service\ImagesService;
use BBC\ProgrammesPagesService\Service\ProgrammesAggregationService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use BBC\ProgrammesPagesService\Service\PromotionsService;
use BBC\ProgrammesPagesService\Service\RelatedLinksService;
use Symfony\Component\HttpFoundation\Request;

/**
 * Top-level Programme Container Page
 *
 * For Top level ProgrammeContainers such as the Doctor Who brand page.
 *
 * We tend to call this "the brand page", but both Brands and Series are both
 * ProgrammeContainers that may appear at the top of the programme hierarchy.
 */
class TlecController extends BaseController
{
    public function __invoke(
        PresenterFactory $presenterFactory,
        Request $request,
        ProgrammeContainer $programme,
        ProgrammesService $programmesService,
        PromotionsService $promotionsService,
        CollapsedBroadcastsService $collapsedBroadcastsService,
        ProgrammesAggregationService $aggregationService,
        ImagesService $imagesService,
        RecEngService $recEngService,
        ElectronService $electronService,
        AdaClassService $adaClassService,
        HelperFactory $helperFactory,
        RelatedLinksService $relatedLinksService,
        FavouritesButtonService $favouritesButtonService
    ) {
        if ($programme->getNetwork() && $programme->getNetwork()->isInternational()) {
            // "International" services are UTC, all others are Europe/London (the default)
            ApplicationTime::setLocalTimeZone('UTC');
        }

        $this->setIstatsProgsPageType('programmes_container');
        $this->setContext($programme);

        $clips = [];
        $galleries = [];

        // TODO check $programme->getPromotionsCount() once it is populated in
        // Faucet to potentially save on a DB query
        $promotions = $promotionsService->findActivePromotionsByContext($programme);

        if ($programme->getOption('show_clip_cards')) {
            $clips = $aggregationService->findStreamableDescendantClips($programme, 4);
        }

        $relatedLinks = [];
        if ($programme->getRelatedLinksCount() > 0) {
            $relatedLinks = $relatedLinksService->findByRelatedToProgramme($programme);
        }

        $upcomingBroadcast = null;
        $onDemandEpisode = null;
        $upcomingRepeatsAndDebutsCounts = ['debuts' => 0, 'repeats' => 0];
        if ($programme->getAggregatedEpisodesCount() > 0) {
            $onDemandEpisodes = $aggregationService->findStreamableOnDemandEpisodes($programme, 1);
            $onDemandEpisode = $onDemandEpisodes[0] ?? null;
            $upcomingBroadcast = $collapsedBroadcastsService
                ->findNextDebutOrRepeatOnByProgrammeWithFullServicesOfNetworksList($programme);
            $upcomingRepeatsAndDebutsCounts = $collapsedBroadcastsService->countUpcomingRepeatsAndDebutsByProgramme($programme);
        }

        if ($programme->getOption('show_gallery_cards')) {
            $galleries = $aggregationService->findDescendantGalleries($programme, 4);
        }

        $lastOn = $this->getLastOn($programme, $collapsedBroadcastsService);

        $comingSoonPromo = $this->getComingSoonPromotion($imagesService, $programme);

        $isVotePriority = $this->isVotePriority($programme);
        $showMiniMap = $this->showMiniMap($request, $programme, $isVotePriority);
        $isPromoPriority = $this->isPromoPriority($programme, $showMiniMap, !empty($promotions));

        $supportingContentItems = $electronService->fetchSupportingContentItemsForProgramme($programme);

        $relatedTopics = [];
        if ($programme->getOption('show_enhanced_navigation')) {
            // Less than 50 episodes (through ancestry)...
            $usePerContainerValues = $programme->getAggregatedEpisodesCount() >= 50;
            $relatedTopics = $adaClassService->findRelatedClassesByContainer($programme, $usePerContainerValues);
        }

        $mapPresenter = $presenterFactory->mapPresenter(
            $programme,
            $upcomingBroadcast,
            $lastOn,
            $promotions[0] ?? null,
            $comingSoonPromo,
            $onDemandEpisode,
            $upcomingRepeatsAndDebutsCounts['debuts'],
            $upcomingRepeatsAndDebutsCounts['repeats'],
            $isPromoPriority,
            $showMiniMap
        );

        // This is ugly but I don't know how else to do it. If promo priority is active the first promo moves
        // into the MAP
        if ($isPromoPriority) {
            array_shift($promotions);
        }

        $recommendations = $recEngService->getRecommendations(
            $programme,
            $onDemandEpisode,
            $upcomingBroadcast ? $upcomingBroadcast->getProgrammeItem() : null,
            $lastOn ? $lastOn->getProgrammeItem() : null,
            2
        );

        $this->setIstatsLabelsForTlec($onDemandEpisode, $upcomingBroadcast, $lastOn);

        $favouritesButton = null;
        if ($programme->isRadio()) {
            $favouritesButton = $favouritesButtonService->getContent();
        }

        return $this->renderWithChrome('find_by_pid/tlec.html.twig', [
            'programme' => $programme,
            'promotions' => $promotions,
            'clips' => $clips,
            'galleries' => $galleries,
            'mapPresenter' => $mapPresenter,
            'isVotePriority' => $isVotePriority,
            'recommendations' => $recommendations,
            'supportingContentItems' => $supportingContentItems,
            'relatedTopics' => $relatedTopics,
            'localised_date_helper' => $helperFactory->getLocalisedDaysAndMonthsHelper(),
            'relatedLinks' => $relatedLinks,
            'favouritesButton' => $favouritesButton,
        ]);
    }

    private function getComingSoonPromotion(ImagesService $imagesService, ProgrammeContainer $programme): ?Promotion
    {
        $comingSoonBlock = $programme->getOption('comingsoon_block');
        if (empty($comingSoonBlock['content']['promotions'])) {
            return null;
        }

        $comingSoon = $comingSoonBlock['content']['promotions'];
        if (!array_key_exists('promotion_title', $comingSoon)) {
            $comingSoon = reset($comingSoon);
        }

        $pid = new Pid($comingSoon['promoted_item_id']);
        $image = $imagesService->findByPid($pid);
        if (is_null($image)) {
            return null; // This should never happen
        }

        $synopses = new Synopses($comingSoon['short_synopsis']);

        return new Promotion(
            $pid,
            $image,
            $comingSoon['promotion_title'],
            $synopses,
            $comingSoon['url'],
            0,
            filter_var($comingSoon['super_promo'], FILTER_VALIDATE_BOOLEAN),
            []
        );
    }

    private function getLastOn(
        ProgrammeContainer $programme,
        CollapsedBroadcastsService $collapsedBroadcastsService
    ): ?CollapsedBroadcast {
        // World News brand pages are the only ones that show the Last On column in the MAP. The Last On column
        // shows a collapsed broadcast, which has a list of networks and services of the broadcasts, which means
        // it needs the full list of services for the networks. If something else starts using the Last On
        // column, this has to be updated, as does the MAP, or else stuff will blow up.
        if ($programme->getNetwork() && $programme->getNetwork()->isWorldNews()) {
            $lastOn = $collapsedBroadcastsService->findPastByProgrammeWithFullServicesOfNetworksList($programme, 1);
        } else {
            $lastOn = $collapsedBroadcastsService->findPastByProgramme($programme, 1);
        }

        return $lastOn[0] ?? null;
    }

    private function isPromoPriority(ProgrammeContainer $programme, bool $showMiniMap, bool $hasPromotions): bool
    {
        return $programme->getOption('brand_layout') === 'promo' && $hasPromotions && $programme->isTlec() && !$showMiniMap;
    }

    private function isVotePriority(ProgrammeContainer $programme): bool
    {
        return $programme->getOption('brand_layout') === 'vote' && $programme->getOption('ivote_block') !== null;
    }

    private function showMiniMap(Request $request, ProgrammeContainer $programme, bool $isVotePriority): bool
    {
        if ($request->query->has('__2016minimap')) {
            return (bool) $request->query->get('__2016minimap');
        }

        if ($isVotePriority) {
            return true;
        }

        return filter_var($programme->getOption('brand_2016_layout_use_minimap'), FILTER_VALIDATE_BOOLEAN);
    }

    private function setIstatsAvailabilityLabel(?ProgrammeItem $onDemandEpisode): void
    {
        if ($onDemandEpisode) {
            $this->setIstatsExtraLabels(['availability' => 'true']);
        } else {
            $this->setIstatsExtraLabels(['availability' => 'false']);
        }
    }

    private function setIstatsUpcomingLabel(?CollapsedBroadcast $upcomingBroadcast): void
    {
        if (empty($upcomingBroadcast)) {
            $this->setIstatsExtraLabels(['upcoming' => 'false']);
        } else {
            $this->setIstatsExtraLabels(['upcoming' => 'true']);
        }
    }

    private function setIstatsLiveEpisode(?CollapsedBroadcast $upcomingBroadcast): void
    {
        if (!empty($upcomingBroadcast) && $upcomingBroadcast->isOnAir()) {
            $this->setIstatsExtraLabels(['live_episode' => 'true']);
        } else {
            $this->setIstatsExtraLabels(['live_episode' => 'false']);
        }
    }

    private function setIstatsPastBroadcastLabel(?CollapsedBroadcast $lastOn) :void
    {
        $hasBroadcastInLast18Months = $lastOn ? $lastOn->getStartAt()->wasWithinLast('18 months') : false;
        if ($hasBroadcastInLast18Months) {
            $this->setIstatsExtraLabels(['past_broadcast' => 'true']);
        } else {
            $this->setIstatsExtraLabels(['past_broadcast' => 'false']);
        }
    }

    private function setIstatsJustMissedLabel(?CollapsedBroadcast $lastOn): void
    {
        if (empty($lastOn)
            || $lastOn->getProgrammeItem()->getStreamableFrom() === null
            || $lastOn->getProgrammeItem()->isStreamable()
        ) {
            $this->setIstatsExtraLabels(['just_missed' => 'false']);
        } elseif ($lastOn->getStartAt()->wasWithinLast('7 days')) {
            // If the broadcast was within the last 7 days but still isn't streamable
            $this->setIstatsExtraLabels(['just_missed' => 'true']);
        }
    }

    private function setIstatsLabelsForTlec(
        ?ProgrammeItem $onDemandEpisode,
        ?CollapsedBroadcast $upcomingBroadcast,
        ?CollapsedBroadcast $lastOn
    ) {
        $this->setIstatsAvailabilityLabel($onDemandEpisode);
        $this->setIstatsUpcomingLabel($upcomingBroadcast);
        $this->setIstatsLiveEpisode($upcomingBroadcast);
        $this->setIstatsPastBroadcastLabel($lastOn);
        $this->setIstatsJustMissedLabel($lastOn);
    }
}
