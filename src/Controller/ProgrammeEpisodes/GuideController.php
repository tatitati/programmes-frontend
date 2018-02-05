<?php
declare(strict_types = 1);
namespace App\Controller\ProgrammeEpisodes;

use App\Controller\BaseController;
use App\Ds2013\Presenters\Utilities\Paginator\PaginatorPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Service\ProgrammesService;

class GuideController extends BaseController
{
    public function __invoke(
        ProgrammeContainer $programme,
        string $extension,
        ProgrammesService $programmesService
    ) {
        $this->setContextAndPreloadBranding($programme);
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

        $this->setIstatsExtraLabels(
            [
                'has_available_items' => count($children) > 0 ? 'true' : 'false',
                'total_available_episodes' => isset($totalChildrenCount) ? (string) $totalChildrenCount : '0',
            ]
        );

        return $this->renderWithChrome('programme_episodes/guide' . $extension . '.html.twig', [
            'programme' => $programme,
            'episodes' => $children,
            'paginatorPresenter' => $paginator,
        ]);
    }
}
