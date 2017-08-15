<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\Season;

class SeasonController extends BaseController
{
    public function __invoke(Season $season)
    {
        $this->setIstatsProgsPageType('seasons_show');
        $this->setContext($season);

        return $this->renderWithChrome('find_by_pid/example_entity.html.twig', [
            'group' => $season,
        ]);
    }
}
