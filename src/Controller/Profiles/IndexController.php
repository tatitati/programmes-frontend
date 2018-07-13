<?php
declare(strict_types = 1);

namespace App\Controller\Profiles;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;

class IndexController extends BaseController
{
    public function __invoke(CoreEntity $coreEntity)
    {
        $this->setContextAndPreloadBranding($coreEntity);

        $parameters = ['coreEntity' => $coreEntity];
        if ($coreEntity instanceof Programme) {
            $parameters['programme'] = $coreEntity; //so the the base 2013 template sets the footer
        }

        return $this->renderWithChrome('profiles/index.html.twig', $parameters);
    }
}
