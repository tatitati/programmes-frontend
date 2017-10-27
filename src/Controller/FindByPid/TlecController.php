<?php
declare(strict_types = 1);

namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use App\DsAmen\PresenterFactory;
use App\RecEng\RecEngService;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\Promotion;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use BBC\ProgrammesPagesService\Service\ImagesService;
use BBC\ProgrammesPagesService\Service\ProgrammesAggregationService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use BBC\ProgrammesPagesService\Service\PromotionsService;
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
        RecEngService $recEngService
    ) {
        $this->setIstatsProgsPageType('programmes_container');
        $this->setContext($programme);

        $clips = [];
        $galleries = [];

        // TODO check $programme->getPromotionsCount() once it is populated in
        // Faucet to potentially save on a DB query
        $promotions = $promotionsService->findActivePromotionsByContext($programme);

        if ($programme->getOption('show_clip_cards')) {
            $clips = $aggregationService->findDescendantClips($programme, 4);
        }

        $upcomingBroadcast = null;
        $streamableEpisodes = null;
        $upcomingRepeatsAndDebutsCounts = ['debuts' => 0, 'repeats' => 0];
        if ($programme->getAggregatedEpisodesCount() > 0) {
            $streamableEpisodes = $aggregationService->findStreamableDescendantEpisodes($programme, 1);
            $upcomingBroadcast = $collapsedBroadcastsService
                ->findNextDebutOrRepeatOnByProgrammeWithFullServicesOfNetworksList($programme);
            $upcomingRepeatsAndDebutsCounts = $collapsedBroadcastsService->countUpcomingRepeatsAndDebutsByProgramme($programme);
        }

        if ($programme->getOption('show_gallery_cards')) {
            $galleries = $aggregationService->findDescendantGalleries($programme, 4);
        }

        $lastOn = $collapsedBroadcastsService->findPastByProgrammeWithFullServicesOfNetworksList($programme, 1);
        $lastOn = $lastOn[0] ?? null;

        $comingSoonPromo = $this->getComingSoonPromotion($imagesService, $programme);

        $isVotePriority = $this->isVotePriority($programme);
        $showMiniMap = $this->showMiniMap($request, $programme, $isVotePriority);
        $isPromoPriority = $this->isPromoPriority($programme, $showMiniMap, $promotions !== null);

        $mapPresenter = $presenterFactory->mapPresenter(
            $programme,
            $upcomingBroadcast,
            $lastOn,
            $promotions[0] ?? null,
            $comingSoonPromo,
            $streamableEpisodes[0] ?? null,
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
            $streamableEpisodes[0] ?? null,
            $upcomingBroadcast ? $upcomingBroadcast->getProgrammeItem() : null,
            $lastOn ? $lastOn->getProgrammeItem() : null,
            2
        );

        return $this->renderWithChrome('find_by_pid/tlec.html.twig', [
            'programme' => $programme,
            'promotions' => $promotions,
            'clips' => $clips,
            'galleries' => $galleries,
            'mapPresenter' => $mapPresenter,
            'isVotePriority' => $isVotePriority,
            'recommendations' => $recommendations,
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
}
