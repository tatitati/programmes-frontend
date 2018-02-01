<?php
declare(strict_types=1);

namespace App\Controller\ProgrammeEpisodes;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Franchise;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IndexController extends BaseController
{
    public function __invoke(CoreEntity $coreEntity)
    {
        // TODO: When Franchise pages are truly gone, remove the following condition and
        // change the method signature to only accept ProgrammeContainers

        // We only serve episodes guide pages for ProgrammeContainers or Franchises
        if (!($coreEntity instanceof Franchise || $coreEntity instanceof ProgrammeContainer)) {
            throw new NotFoundHttpException('Only ProgrammeContainers and Franchises have episode guides');
        }

        if ($coreEntity instanceof Franchise ||
            $coreEntity instanceof ProgrammeContainer && $coreEntity->getAvailableEpisodesCount()) {
            $route = 'programme_episodes_player';
        } else {
            $route = 'programme_episodes_guide';
        }

        $this->response()->setPublic()->setMaxAge(600);
        return $this->cachedRedirectToRoute(
            $route,
            ['pid' => $coreEntity->getPid()]
        );
    }
}
