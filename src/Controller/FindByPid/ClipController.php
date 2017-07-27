<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;

class ClipController extends BaseController
{
    public function __invoke(Clip $clip)
    {
        $this->setContext($clip);

        return $this->renderWithChrome('find_by_pid/example_entity.html.twig', [
            'programme' => $clip,
        ]);
    }
}
