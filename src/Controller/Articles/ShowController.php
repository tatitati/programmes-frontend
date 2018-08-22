<?php
declare(strict_types = 1);

namespace App\Controller\Articles;

use App\Controller\BaseController;
use App\Controller\Helpers\IsiteKeyHelper;
use App\ExternalApi\Isite\Service\IsiteService;
use BBC\ProgrammesPagesService\Service\CoreEntitiesService;
use Symfony\Component\HttpFoundation\Request;

class ShowController extends BaseController
{
    public function __invoke(string $key, string $slug, Request $request, IsiteService $isiteService, IsiteKeyHelper $isiteKeyHelper, CoreEntitiesService $coreEntitiesService)
    {
        $preview = false;
        if ($request->query->has('preview') && $request->query->get('preview')) {
            $preview = true;
        }

        if ($isiteKeyHelper->isKeyAGuid($key)) {
            return $this->redirectWith($isiteKeyHelper->convertGuidToKey($key), $slug, $preview);
        }

        return $this->renderWithChrome('articles/show.html.twig');
    }

    private function redirectWith(string $key, string $slug, bool $preview)
    {
        $params = ['key' => $key, 'slug' => $slug];

        if ($preview) {
            $params['preview'] = 'true';
        }

        return $this->cachedRedirectToRoute('programme_article', $params, 301);
    }
}
