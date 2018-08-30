<?php
declare(strict_types = 1);

namespace App\Controller\Articles;

use App\Controller\BaseController;
use App\Controller\Helpers\IsiteKeyHelper;
use App\ExternalApi\Isite\Domain\Article;
use App\ExternalApi\Isite\IsiteResult;
use App\ExternalApi\Isite\Service\ArticleService;
use BBC\ProgrammesPagesService\Service\CoreEntitiesService;
use Symfony\Component\HttpFoundation\Request;

class ShowController extends BaseController
{
    public function __invoke(string $key, string $slug, Request $request, ArticleService $isiteService, IsiteKeyHelper $isiteKeyHelper, CoreEntitiesService $coreEntitiesService)
    {
        $preview = false;
        if ($request->query->has('preview') && $request->query->get('preview')) {
            $preview = true;
        }

        if ($isiteKeyHelper->isKeyAGuid($key)) {
            return $this->redirectWith($isiteKeyHelper->convertGuidToKey($key), $slug, $preview);
        }

        $guid = $isiteKeyHelper->convertKeyToGuid($key);

        /** @var IsiteResult $isiteResult */
        $isiteResult = $isiteService->getByContentId($guid, $preview)->wait(true);

        $articles = $isiteResult->getDomainModels();
        if (!$articles) {
            throw $this->createNotFoundException('No articles found for guid');
        }

        /** @var Article $article */
        $article = reset($articles);

        if ($slug !== $article->getSlug()) {
            return $this->redirectWith($article->getKey(), $article->getSlug(), $preview);
        }

        $context = null;
        if (!empty($article->getParentPid())) {
            $context = $coreEntitiesService->findByPidFull($article->getParentPid());

            if ($article->getProjectSpace() !== $context->getOption('project_space')) {
                throw $this->createNotFoundException('Project space Article-Programme not matching');
            }
        }

        $this->setContext($context);

        if ('' !== $article->getBrandingId()) {
            $this->setBrandingId($article->getBrandingId());
        }

        $childPromise = $isiteService->setChildProfilesOn([$article], $article->getProjectSpace());
        $this->resolvePromises([$childPromise]);

        return $this->renderWithChrome('articles/show.html.twig', ['article' => $article]);
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
