<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Service\ContributionsService;

class EpisodeController extends BaseController
{
    public function __invoke(Episode $episode, ContributionsService $contributionsService)
    {
        $this->setIstatsProgsPageType('programmes_episode');
        $this->setContextAndPreloadBranding($episode);

        $contributions = [];
        if ($episode->getContributionsCount() > 0) {
            $contributions = $contributionsService->findByContributionToProgramme($episode);
        }

        return $this->renderWithChrome('find_by_pid/episode.html.twig', [
            'contributions' => $contributions,
            'programme' => $episode,
        ]);
    }
}
