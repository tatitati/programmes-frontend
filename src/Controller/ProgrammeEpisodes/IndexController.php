<?php
declare(strict_types=1);

namespace App\Controller\ProgrammeEpisodes;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;

class IndexController extends BaseController
{
    public function __invoke(ProgrammeContainer $programmeContainer)
    {
        if ($programmeContainer->getAvailableEpisodesCount()) {
            $route = 'programme_episodes_player';
        } else {
            $route = 'programme_episodes_guide';
        }

        $this->response()->setPublic()->setMaxAge(600);
        return $this->cachedRedirectToRoute(
            $route,
            ['pid' => $programmeContainer->getPid()]
        );
    }
}
