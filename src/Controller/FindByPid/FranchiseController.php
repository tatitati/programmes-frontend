<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\Franchise;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FranchiseController extends BaseController
{
    public function __invoke(Franchise $franchise)
    {
        // We don't support franchise pages anymore, but because we'll be linking to v2 franchise pages
        // (see ProgrammeEpisodes/IndexController) we still need to be able to resolve
        // the argument in ContextEntityByPidValueResolver

        throw new NotFoundHttpException(
            sprintf('The item with PID "%s" was a franchise, which v3 does not support', $franchise->getPid())
        );
    }
}
