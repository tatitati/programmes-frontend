<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Service\ContributionsService;
use BBC\ProgrammesPagesService\Service\ProgrammesAggregationService;

class EpisodeController extends BaseController
{
    public function __invoke(Episode $episode, ContributionsService $contributionsService, ProgrammesAggregationService $aggregationService)
    {
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

        return $this->renderWithChrome('find_by_pid/episode.html.twig', [
            'contributions' => $contributions,
            'programme' => $episode,
            'clips' => $clips,
        ]);
    }
}
