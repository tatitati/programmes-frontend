<?php
declare(strict_types = 1);
namespace App\Controller\ProgrammeEpisodes;

use App\Controller\BaseController;
use App\Controller\Traits\IndexerTrait;
use App\Ds2013\PresenterFactory;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use Symfony\Component\HttpFoundation\Request;

class GuidePartialController extends BaseController
{
    use IndexerTrait;

    public function __invoke(
        ProgrammeContainer $programme,
        PresenterFactory $presenterFactory,
        ProgrammesService $programmesService,
        CollapsedBroadcastsService $collapsedBroadcastService,
        Request $request
    ) {
        $nestedLevel = $request->query->get('nestedlevel');
        $this->setInternationalStatusAndTimezoneFromContext($programme);

        $children = [];
        $totalChildrenCount = $programmesService->countEpisodeGuideChildren($programme);
        if ($totalChildrenCount > 0) {
            $children = $programmesService->findEpisodeGuideChildren($programme, GuideController::LIMIT);
        }

        $upcomingBroadcasts = $this->getUpcomingBroadcastsIndexedByProgrammePid($programme, $collapsedBroadcastService);

        return $this->render('programme_episodes/guide.2013inc.html.twig', [
            'programme' => $programme,
            'children' => $children,
            'totalChildrenCount' => $totalChildrenCount,
            'upcomingBroadcasts' => $upcomingBroadcasts,
            'nestedlevel' => (is_null($nestedLevel)) ? 1 : (int) $nestedLevel,
        ], $this->response());
    }
}
