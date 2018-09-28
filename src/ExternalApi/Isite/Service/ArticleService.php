<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Service;

use App\ExternalApi\Isite\Domain\Article;
use App\ExternalApi\Isite\SearchQuery;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use Closure;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class ArticleService extends IsiteService
{
    public function getArticlesByProgramme(
        Programme $programme,
        int $page = 1,
        int $limit = 48
    ): PromiseInterface {
        return $this->getByProgramme('article', $programme, $page, $limit);
    }

    /**
     * @param Article[] $articles
     * @param string $project
     * @param int $page
     * @param int $limit
     * @return PromiseInterface
     */
    public function setChildProfilesOn(
        array $articles,
        string $project,
        int $page = 1,
        int $limit = 48
    ): PromiseInterface {
        if (empty($articles)) {
            return new FulfilledPromise([]);
        }

        $cacheKeys = [];
        $urls = [];
        foreach ($articles as $article) {
            $query = $this->getBaseQuery($project, $page, $limit);
            $query->setQuery(['article:parent', '=', 'urn:isite:' . $project . ':' . $article->getFileId()]);

            $urls[] = $this->baseUrl . $query->getPath();
            $cacheKeys[] = $article->getFileId();
        }

        $cacheKey = $this->clientFactory->keyHelper(__CLASS__, __FUNCTION__, implode(',', $cacheKeys), $page, $limit);

        $client = $this->clientFactory->getHttpApiMultiClient(
            $cacheKey,
            $urls,
            Closure::fromCallable([$this, 'parseResponses']),
            [$articles],
            [],
            CacheInterface::NORMAL,
            CacheInterface::NONE,
            [
                'timeout' => 10,
            ]
        );

        $promise = $client->makeCachedPromise();
        return $this->chainHydrationPromise($articles, $promise);
    }

    protected function getBaseQuery(string $project, int $page, int $limit): SearchQuery
    {
        $query = new SearchQuery();
        $query->setNamespace('article', $project)
            ->setProject($project)
            ->setDepth(0)// no depth as this is an aggregation - no need to fetch parents or content blocks
            ->setSort($this->getDefaultSort())
            ->setPage($page)
            ->setPageSize($limit);

        return $query;
    }

    private function getDefaultSort(): array
    {
        return [
            [
                'elementPath' => '/article:form/article:metadata/article:position',
                'type' => 'numeric',
                'direction' => 'asc',
            ],
            [
                'elementPath' => '/article:form/article:metadata/article:title',
                'direction' => 'asc',
            ],
        ];
    }
}
