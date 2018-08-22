<?php
declare(strict_types = 1);

namespace App\Controller\Articles;

use App\Controller\BaseController;
use App\ExternalApi\Isite\Service\IsiteService;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;

class IndexController extends BaseController
{
    public function __invoke(CoreEntity $coreEntity, IsiteService $isiteService)
    {
        $this->setContextAndPreloadBranding($coreEntity);

        $articles = [];
        $parameters = ['coreEntity' => $coreEntity, 'articles' => $articles, 'paginatorPresenter' => null];
        if ($coreEntity instanceof Programme) {
            $parameters['programme'] = $coreEntity; //so the the base 2013 template sets the footer
        }

        if (empty($articles)) {
            $this->response()->setStatusCode(404);
        }

        return $this->renderWithChrome('articles/index.html.twig', $parameters);
    }
}
