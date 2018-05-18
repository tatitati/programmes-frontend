<?php
declare(strict_types=1);

namespace App\ExternalApi\RmsPodcast\Service;

use App\ExternalApi\Client\HttpApiClient;
use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\RmsPodcast\Domain\RmsPodcast;
use App\ExternalApi\RmsPodcast\Domain\RmsPodcastApiResult;
use App\ExternalApi\RmsPodcast\Mapper\RmsPodcastMapper;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;

class RmsPodcastService
{
    /** @var string */
    private $baseUrl;

    /** @var string */
    private $apiKey;

    /** @var HttpApiClientFactory */
    private $clientFactory;

    /** @var RmsPodcastMapper */
    private $mapper;

    public function __construct(string $baseUrl, string $apiKey, HttpApiClientFactory $clientFactory, RmsPodcastMapper $mapper)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        $this->clientFactory = $clientFactory;
        $this->mapper = $mapper;
    }

    public function getPodcast(Pid $pid): PromiseInterface
    {
        $client = $this->makeClient($pid);
        return $client->makeCachedPromise();
    }

    private function makeClient($pid): HttpApiClient
    {
        $cacheKey = $this->clientFactory->keyHelper(__CLASS__, __FUNCTION__, (string) $pid);

        return $this->clientFactory->getHttpApiClient(
            $cacheKey,
            $this->baseUrl . '/' . (string) $pid . '.json',
            Closure::fromCallable([$this, 'procesSuccessResponse']),
            [],
            null,
            CacheInterface::MEDIUM,
            CacheInterface::NORMAL,
            ['headers' => ['X-API-KEY' => $this->apiKey]]
        );
    }

    private function procesSuccessResponse(Response $response): RmsPodcast
    {
        return $this->mapper->mapItem($response->getBody()->getContents());
    }
}
