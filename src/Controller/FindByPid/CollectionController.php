<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\Collection;

class CollectionController extends BaseController
{
    public function __invoke(Collection $collection)
    {
        $this->setContext($collection);

        return $this->renderWithChrome('find_by_pid/example_entity.html.twig', [
            'group' => $collection,
        ]);
    }
}
