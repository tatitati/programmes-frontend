<?php
declare(strict_types=1);

namespace App\ExternalApi\Ada\Service;

use App\ExternalApi\Ada\Mapper\AdaProgrammeMapper;
use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\Exception\ParseException;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use Closure;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Cache\CacheItemInterface;

class AdaProgrammeService
{
    /** @var HttpApiClientFactory */
    private $clientFactory;

    /** @var string */
    private $baseUrl;

    /** @var AdaProgrammeMapper */
    private $mapper;

    /** @var ProgrammesService */
    private $programmesService;

    /** @var CacheInterface */
    private $cache;

    public function __construct(
        HttpApiClientFactory $clientFactory,
        string $baseUrl,
        AdaProgrammeMapper $mapper,
        ProgrammesService $programmesService,
        CacheInterface $cache
    ) {
        $this->clientFactory = $clientFactory;
        $this->baseUrl = $baseUrl;
        $this->mapper = $mapper;
        $this->programmesService = $programmesService;
        $this->cache = $cache;
    }

    public function findSuggestedByProgrammeItem(Programme $programme, int $limit = 3): PromiseInterface
    {
        $cacheKey = $this->clientFactory->keyHelper(__CLASS__, __FUNCTION__, (string) $programme->getPid(), $limit);
        $cacheItem = $this->cache->getItem($cacheKey);
        if ($cacheItem->isHit()) {
            return new FulfilledPromise($cacheItem->get());
        }
        $promises = [
            'relatedByTag' => $this->requestRelatedProgrammeItems($programme->getPid(), 'tag', null, 1),
            'relatedByBrand' => $this->requestRelatedProgrammeItems($programme->getPid(), null, $programme->getTleo()->getPid(), 1),
            'relatedByCategory' => $this->requestRelatedProgrammeItems($programme->getPid(), null, null, 5),
        ];

        $aggregatePromise = \GuzzleHttp\Promise\all($promises);
        $aggregatePromise = $aggregatePromise->then(
            //onFulfilled
            function ($results) use ($cacheItem, $limit) {
                // $results contains the results of all promises in $promises indexed by the keys supplied in
                // that array
                return $this->parseAggregateResponses($results, $cacheItem, $limit);
            },
            // onRejected
            function ($reason) {
                // Logging should already have been handled by HttpApiClient. If any request fails
                // return nothing and get on with our lives, data is not critical here
                return [];
            }
        );
        return $aggregatePromise;
    }

    private function parseAggregateResponses(array $results, CacheItemInterface $cacheItem, int $limit): array
    {
        $uniqueProgrammes = array_unique(array_merge($results['relatedByTag'], $results['relatedByBrand'], $results['relatedByCategory']), SORT_REGULAR);
        $uniqueProgrammes = array_slice($uniqueProgrammes, 0, $limit);

        if (empty($uniqueProgrammes)) {
            $this->cache->setItem($cacheItem, [], CacheInterface::NORMAL);
            return [];
        }

        $pids = [];
        foreach ($uniqueProgrammes as $uniqueProgramme) {
            $pids[] = new Pid($uniqueProgramme['pid']);
        }
        // Collect all the Programmes objects from the Programmes service using the pids array
        $programmeItems = $this->programmesService->findByPids($pids);
        $relatedProgrammes = [];
        foreach ($uniqueProgrammes as $key => $item) {
            $relatedProgrammes[] = $this->mapper->mapItem($programmeItems[$key], $item);
        }

        $this->cache->setItem($cacheItem, $relatedProgrammes, CacheInterface::MEDIUM);
        return $relatedProgrammes;
    }

    private function requestRelatedProgrammeItems(Pid $pid, ?string $scope = null, ?Pid $countContextPid = null, int $limit = 10, int $page = 1)
    {
        $order = 'random';
        $orderDirection = null;
        $threshold = 2;

        $params = http_build_query(
            [
                'page' => $page,
                'page_size' => $limit,
                'scope' => $scope,
                'count_context' => $countContextPid,
                'threshold' => $threshold,
                'order' => $order,
            ]
        );
        $url =  $this->baseUrl . '/programme_items/' . $pid . '/related?' . $params;

        $cacheKey = $this->clientFactory->keyHelper(__CLASS__, __FUNCTION__, $pid, $scope, $countContextPid, $threshold, $order, $orderDirection, $limit, $page);

        $client = $this->clientFactory->getHttpApiClient(
            $cacheKey,
            $url,
            Closure::fromCallable([$this, 'parseSingleResponse']),
            [$countContextPid, $limit],
            [],
            CacheInterface::MEDIUM,
            CacheInterface::NORMAL,
            [
                'timeout' => 10,
            ]
        );
        return $client->makeCachedPromise();
    }

    private function parseSingleResponse(Response $response): array
    {
        $data = json_decode($response->getBody()->getContents(), true);
        if (!isset($data['items'])) {
            throw new ParseException("Ada JSON response does not contain items element");
        }
        return $data['items'];
    }
}
