<?php
declare(strict_types = 1);

namespace App\Controller\Articles;

use App\Controller\BaseController;
use App\Controller\Helpers\IsiteKeyHelper;
use App\Ds2013\Presenters\Utilities\Paginator\PaginatorPresenter;
use App\ExternalApi\Isite\Domain\Article;
use App\ExternalApi\Isite\IsiteResult;
use App\ExternalApi\Isite\Service\ArticleService;
use BBC\ProgrammesPagesService\Service\CoreEntitiesService;
use Exception;
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

        $isiteResult = null;
        try {
            /** @var IsiteResult $isiteResult */
            $isiteResult = $isiteService->getByContentId($guid, $preview)->wait(true);
        } catch (Exception $e) {
            return $this->redirectToRoute('programme_article', ['contactForm' => 1, 'key' => $key, 'slug' => $slug] ,307);
        }

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

        $parents = $article->getParents();
        $siblingPromise = $isiteService->setChildrenOn($parents, $article->getProjectSpace()); //if more than 48, extras are removed
        $childPromise = $isiteService->setChildrenOn([$article], $article->getProjectSpace(), $this->getPage());
        $response = $this->resolvePromises(['children' => $childPromise, 'siblings' => $siblingPromise]);

        $paginator = $this->getPaginator(reset($response['children']));
        return $this->renderWithChrome('articles/show.html.twig', ['article' => $article, 'paginatorPresenter' => $paginator]);
    }

    private function redirectWith(string $key, string $slug, bool $preview)
    {
        $params = ['key' => $key, 'slug' => $slug];

        if ($preview) {
            $params['preview'] = 'true';
        }

        return $this->cachedRedirectToRoute('programme_article', $params, 301);
    }

    private function getPaginator(IsiteResult $iSiteResult): ?PaginatorPresenter
    {
        if ($iSiteResult->getTotal() <= 48) {
            return null;
        }

        return new PaginatorPresenter($this->getPage(), 48, $iSiteResult->getTotal());
    }
}
