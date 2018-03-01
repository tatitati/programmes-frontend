<?php
declare(strict_types = 1);
namespace App\Controller\ProgrammeEpisodes;

use App\Ds2013\PresenterFactory;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use BBC\ProgrammesPagesService\Service\ProgrammesAggregationService;

class UpcomingController extends BaseProgrammeEpisodesController
{
    public function __invoke(
        ProgrammeContainer $programme,
        ?string $debut,
        CollapsedBroadcastsService $collapsedBroadcastsService,
        PresenterFactory $presenterFactory,
        ProgrammesAggregationService $programmeAggregationService
    ) {
        $this->setContextAndPreloadBranding($programme);
        $this->setIstatsProgsPageType('broadcast_slice');
        $this->setInternationalStatusAndTimezoneFromContext($programme);

        $subNavPresenter = $this->getSubNavPresenter($collapsedBroadcastsService, $programme, $presenterFactory);
        $upcomingBroadcasts = $collapsedBroadcastsService->findUpcomingByProgrammeWithFullServicesOfNetworksList($programme, 100);

        // display only debut broadcast if $debut is set
        if ($debut) {
            foreach ($upcomingBroadcasts as $key => $upcoming) {
                if ($upcoming->isRepeat()) {
                    unset($upcomingBroadcasts[$key]);
                }
            }
        }

        return $this->renderWithChrome('programme_episodes/upcoming.html.twig', [
            'programme' => $programme,
            'upcomingBroadcasts' => $upcomingBroadcasts,
            'subNavPresenter' => $subNavPresenter,
            'debut' => $debut,
        ]);
    }
}
