<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Service;

use App\ExternalApi\Isite\Domain\Profile;
use App\ExternalApi\Isite\IsiteResult;
use App\ExternalApi\Isite\SearchQuery;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use Closure;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class ProfileService extends IsiteService
{
    /**
     * @param Profile[] $profiles
     * @param string $project
     * @param int $page
     * @param int $limit
     * @return PromiseInterface
     */
    public function setChildProfilesOn(
        array $profiles,
        string $project,
        int $page = 1,
        int $limit = 48
    ): PromiseInterface {
        if (empty($profiles)) {
            return new FulfilledPromise([]);
        }

        $cacheKeys = [];
        $urls = [];
        foreach ($profiles as $profile) {
            $query = $this->getBaseQuery($project, $page, $limit);
            $query->setQuery(['profile:parent', '=', 'urn:isite:' . $project . ':' . $profile->getFileId()]);

            $urls[] = $this->baseUrl . $query->getPath();
            $cacheKeys[] = $profile->getFileId();
        }

        $cacheKey = $this->clientFactory->keyHelper(__CLASS__, __FUNCTION__, implode(',', $cacheKeys), $page, $limit);

        $client = $this->clientFactory->getHttpApiMultiClient(
            $cacheKey,
            $urls,
            Closure::fromCallable([$this, 'parseResponses']),
            [$profiles],
            [],
            CacheInterface::NORMAL,
            CacheInterface::NONE,
            [
                'timeout' => 10,
            ]
        );

        $promise = $client->makeCachedPromise();
        return $this->chainHydrationPromise($profiles, $promise);
    }

    public function getProfilesByProgramme(
        Programme $programme,
        int $page = 1,
        int $limit = 48
    ): PromiseInterface {
        /** @var string $project */
        $project = $programme->getOption('project_space');
        $query = $this->getBaseQuery($project, $page, $limit);
        $query->setQuery([
            'and' => [
                ['profile:parent_pid', '=', (string) $programme->getPid()],
                [
                    'not' => [
                        ['profile:parent', 'contains', 'urn:isite'],
                    ],
                ],
            ],
        ]);

        $url = $this->baseUrl . $query->getPath();

        $cacheKey = $this->clientFactory->keyHelper(__CLASS__, __FUNCTION__, $programme->getPid(), $page, $limit);

        $client = $this->clientFactory->getHttpApiMultiClient(
            $cacheKey,
            [$url],
            Closure::fromCallable([$this, 'parseResponse']),
            [],
            new IsiteResult($page, $limit, 0, []),
            CacheInterface::NORMAL,
            CacheInterface::NONE,
            [
                'timeout' => 10,
            ]
        );

        return $client->makeCachedPromise();
    }

    protected function getBaseQuery(string $project, int $page, int $limit): SearchQuery
    {
        $query = new SearchQuery();
        $query->setNamespace('profile', $project)
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
                'elementPath' => '/profile:form/profile:metadata/profile:position',
                'type' => 'numeric',
                'direction' => 'asc',
            ],
            [
                'elementPath' => '/profile:form/profile:metadata/profile:title',
                'direction' => 'asc',
            ],
        ];
    }
}
