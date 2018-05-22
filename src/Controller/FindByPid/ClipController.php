<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;

class ClipController extends BaseController
{
    public function __invoke(Clip $clip)
    {
        $this->setIstatsProgsPageType('programmes_clip');
        $this->setContextAndPreloadBranding($clip);

        return $this->renderWithChrome('find_by_pid/clip.html.twig', [
            'programme' => $clip,
        ]);
    }
}
