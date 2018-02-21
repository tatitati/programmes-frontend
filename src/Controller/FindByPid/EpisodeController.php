<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;

class EpisodeController extends BaseController
{
    public function __invoke(Episode $episode)
    {
        $this->setIstatsProgsPageType('programmes_episode');
        $this->setContextAndPreloadBranding($episode);

        return $this->renderWithChrome('find_by_pid/episode.html.twig', [
            'programme' => $episode,
        ]);
    }
}
