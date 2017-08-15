<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\Franchise;

class FranchiseController extends BaseController
{
    public function __invoke(Franchise $franchise)
    {
        $this->setIstatsProgsPageType('franchises_show');
        $this->setContext($franchise);

        return $this->renderWithChrome('find_by_pid/example_entity.html.twig', [
            'group' => $franchise,
        ]);
    }
}
