<?php
declare(strict_types = 1);

namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use App\DsAmen\PresenterFactory;
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
use Exception;
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
        ImagesService $imagesService
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

        $upcomingEpisodes = null;
        $streamableEpisodes = null;
        if ($programme->getAggregatedEpisodesCount() > 0) {
            $streamableEpisodes = $aggregationService->findStreamableDescendantEpisodes($programme, 1);
            if (empty($streamableEpisodes)) {
                $upcomingEpisodes = $aggregationService->findUpcomingStreamableDescendantEpisodes($programme, 1);
            }
        }

        if ($programme->getOption('show_gallery_cards')) {
            $galleries = $aggregationService->findDescendantGalleries($programme, 4);
        }

        $upcomingBroadcast = null;
        if ($programme->getAggregatedEpisodesCount()) {
            $upcomingBroadcast = $collapsedBroadcastsService
                ->findNextDebutOrRepeatOnByProgrammeWithFullServicesOfNetworksList($programme);
        }

        $upcomingRepeatsAndDebutsCounts = $collapsedBroadcastsService->countUpcomingRepeatsAndDebutsByProgramme($programme);

        $lastOn = $collapsedBroadcastsService->findPastByProgrammeWithFullServicesOfNetworksList($programme, 1);
        $lastOn = $lastOn[0] ?? null;

        $comingSoonPromo = $this->getComingSoonPromotion($imagesService, $programme);

        $mapPresenter = $presenterFactory->mapPresenter(
            $request,
            $programme,
            $upcomingBroadcast,
            $lastOn,
            $comingSoonPromo,
            $streamableEpisodes[0] ?? null,
            $upcomingEpisodes[0] ?? null,
            $upcomingRepeatsAndDebutsCounts['debuts'],
            $upcomingRepeatsAndDebutsCounts['repeats']
        );

        return $this->renderWithChrome('find_by_pid/tlec.html.twig', [
            'programme' => $programme,
            'promotions' => $promotions,
            'clips' => $clips,
            'galleries' => $galleries,
            'mapPresenter' => $mapPresenter,
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
}
