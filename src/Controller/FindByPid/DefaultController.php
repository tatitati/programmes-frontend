<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;

class DefaultController extends BaseController
{
    /**
     * I am a placeholder so we can show a page for programmes that we have not
     * yet started working on. Once we have completed all FindByPid routes, this
     * should be removed.
     */
    public function __invoke(Programme $programme)
    {
        $this->setContext($programme);
        return $this->renderWithChrome('find_by_pid/example_programme.html.twig', [
            'programme' => $programme,
        ]);
    }
}
