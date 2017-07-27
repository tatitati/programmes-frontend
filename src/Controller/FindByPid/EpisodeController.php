<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;

class EpisodeController extends BaseController
{
    public function __invoke(Episode $episode)
    {
        $this->setContext($episode);

        return $this->renderWithChrome('find_by_pid/example_entity.html.twig', [
            'programme' => $episode,
        ]);
    }
}
