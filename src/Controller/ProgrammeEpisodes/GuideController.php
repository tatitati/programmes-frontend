<?php
declare(strict_types=1);

namespace App\Controller\ProgrammeEpisodes;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;

class GuideController extends BaseController
{
    public function __invoke(ProgrammeContainer $programmeContainer)
    {
        $this->setContextAndPreloadBranding($programmeContainer);
        return $this->renderWithChrome('programme_episodes/guide.html.twig', []);
    }
}
