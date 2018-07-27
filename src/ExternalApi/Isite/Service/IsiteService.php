<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Service;

use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\Isite\Domain\Profile;
use App\ExternalApi\Isite\GuidQuery;
use App\ExternalApi\Isite\IsiteFeedResponseHandler;
use App\ExternalApi\Isite\IsiteResult;
use App\ExternalApi\Isite\SearchQuery;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use Closure;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;

class IsiteService
{
    /** @var string */
    private $baseUrl;

    /** @var HttpApiClientFactory */
    private $clientFactory;

    /** @var IsiteFeedResponseHandler */
    private $responseHandler;

    public function __construct(
        string $baseUrl,
        HttpApiClientFactory $clientFactory,
        IsiteFeedResponseHandler $responseHandler
    ) {
        $this->baseUrl = $baseUrl;
        $this->clientFactory = $clientFactory;
        $this->responseHandler = $responseHandler;
    }

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
            return new FulfilledPromise(null);
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
            Closure::fromCallable([$this, 'parseChildrenOfProfilesResponses']),
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

    public function getByContentId(string $guid, bool $preview = false): PromiseInterface
    {
        $guidQuery = new GuidQuery();
        $guidQuery
            ->setContentId($guid)
            ->setDepth(1);

        if ($preview) {
            $guidQuery
                ->setPreview(true)
                ->setAllowNonLive(true);
        }

        $cacheKey = $this->clientFactory->keyHelper(__CLASS__, __FUNCTION__, $guid, $preview);

        $client = $this->clientFactory->getHttpApiMultiClient(
            $cacheKey,
            [$this->baseUrl . $guidQuery->getPath()],
            Closure::fromCallable([$this, 'parseProfileResponse']),
            [],
            new IsiteResult(1, 1, 0, []),
            CacheInterface::NORMAL,
            CacheInterface::NONE,
            [
                'timeout' => 10,
            ]
        );

        return $client->makeCachedPromise();
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
            Closure::fromCallable([$this, 'parseProfileResponse']),
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

    /**
     * @param Response[] $responses
     * @param Profile[] $profiles
     * @return IsiteResult[]
     */
    public function parseChildrenOfProfilesResponses(array $responses, array $profiles): array
    {
        $results = [];
        foreach ($responses as $key => $response) {
            $results[] = $this->responseHandler->getIsiteResult($response);
        }
        return $results;
    }

    /**
     * @param Response[] $responses
     * @return IsiteResult
     */
    public function parseProfileResponse(array $responses): IsiteResult
    {
        return $this->responseHandler->getIsiteResult($responses[0]);
    }

    private function getBaseQuery(string $project, int $page, int $limit): SearchQuery
    {
        $query = new SearchQuery();
        $query->setNamespace($project)
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

    /**
     * @param Profile[] $profiles
     * @param PromiseInterface $promise
     * @return PromiseInterface
     */
    private function chainHydrationPromise(array $profiles, PromiseInterface $promise): PromiseInterface
    {
        return $promise->then(
            function ($responses) use ($profiles) {
                // Success callback
                $this->hydrateProfiles($profiles, $responses);
            },
            function ($error) use ($profiles) {
                // Error callback
                $this->hydrateProfiles($profiles, []);
            }
        );
    }

    private function hydrateProfiles(array $profiles, array $responses): void
    {
        foreach ($profiles as $key => $profile) {
            $childProfiles = (isset($responses[$key]) ? $responses[$key]->getDomainModels() : []);
            $profile->setChildren($childProfiles);
        }
    }
}
