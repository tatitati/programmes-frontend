<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Service\ProgrammesService;

/**
 * Top-level Programme Container Page
 *
 * For Top level ProgrammeContainers such as the Doctor Who brand page.
 *
 * We tend to call this "the brand page", but both Brands and Series are both
 * ProgrammeContainers that may appear at the top of the programme hierarchy.
 */
class TlecController extends BaseController
{
    public function __invoke(ProgrammeContainer $programme, ProgrammesService $programmesService)
    {
        $this->setContext($programme);

        return $this->renderWithChrome('find_by_pid/tlec.html.twig', [
            'programme' => $programme,
        ]);
    }
}
