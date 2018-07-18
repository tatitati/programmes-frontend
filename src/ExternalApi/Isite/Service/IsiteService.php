<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Service;

use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\Isite\Domain\Profile;
use App\ExternalApi\Isite\IsiteFeedResponseHandler;
use App\ExternalApi\Isite\IsiteResult;
use App\ExternalApi\Isite\SearchQuery;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use Closure;
use GuzzleHttp\Promise\FulfilledPromise;
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

    public function __construct(string $baseUrl, HttpApiClientFactory $clientFactory, IsiteFeedResponseHandler $responseHandler)
    {
        $this->baseUrl = $baseUrl;
        $this->clientFactory = $clientFactory;
        $this->responseHandler = $responseHandler;
    }

    /**
     * @param string $project
     * @param Profile[] $profiles
     * @param int $page
     * @param int $limit
     * @return PromiseInterface
     */
    public function getChildrenOfProfiles(
        string $project,
        array $profiles,
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
            Closure::fromCallable([$this, 'parseChildrenOfProfilesResponses']),
            [$profiles],
            [],
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
            Closure::fromCallable([$this, 'parseProfilesByProgrammeResponses']),
            [],
            [],
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
     */
    public function parseChildrenOfProfilesResponses(array $responses, array $profiles): void
    {
        foreach ($responses as $key => $response) {
            $result = $this->responseHandler->getIsiteResult($response);
            $profiles[$key]->setChildren($result->getDomainModels());
        }
    }

    /**
     * @param Response[] $responses
     * @return IsiteResult
     */
    public function parseProfilesByProgrammeResponses(array $responses): IsiteResult
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
}
