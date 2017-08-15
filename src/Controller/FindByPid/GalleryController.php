<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\Gallery;

class GalleryController extends BaseController
{
    public function __invoke(Gallery $gallery)
    {
        $this->setIstatsProgsPageType('galleries_show');
        $this->setContext($gallery);

        return $this->renderWithChrome('find_by_pid/example_entity.html.twig', [
            'group' => $gallery,
        ]);
    }
}
