<?php
declare(strict_types = 1);

namespace App\ExternalApi\Client;

use App\ExternalApi\Exception\MultiParseException;
use App\ExternalApi\Exception\ParseException;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use Closure;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;

class HttpApiMultiClient extends HttpApiClient
{
    /** @var array */
    private $requestUrls;

    /**
     * @param ClientInterface $client
     * @param CacheInterface $cache
     * @param LoggerInterface $logger
     * @param string $cacheKey
     *   pre-generated cacheKey. Use HttpApiClientFactory->keyHelper()
     * @param array $requestUrls
     * @param callable $parseResponse
     *   a callable function accepting a Guzzle Response object as its first parameter
     * @param array $parseResponseArguments
     *   an array containing arguments 2-n required by function in $parseResponse
     * @param mixed $nullResult
     *   the result you want returned on a 404 or HTTP error
     * @param int|string $standardTTL
     *   TTL for a 200 API response
     * @param int|string $notFoundTTL
     *   TTL for a 404 API response
     * @param array $guzzleOptions
     *   Extra options for guzzle client
     */
    public function __construct(
        ClientInterface $client,
        CacheInterface $cache,
        LoggerInterface $logger,
        string $cacheKey,
        array $requestUrls,
        callable $parseResponse,
        array $parseResponseArguments,
        $nullResult,
        $standardTTL,
        $notFoundTTL,
        array $guzzleOptions
    ) {
        $this->requestUrls = $requestUrls;
        // Hack. We need to log A request URL, we don't always know which one failed, so pick the first one
        $requestUrl = reset($requestUrls);
        parent::__construct($client, $cache, $logger, $cacheKey, $requestUrl, $parseResponse, $parseResponseArguments, $nullResult, $standardTTL, $notFoundTTL, $guzzleOptions);
    }

    public function makeCachedRequest()
    {
        $promise = $this->makeCachedPromise();
        return $promise->wait(true);
    }

    public function makeCachedPromise(): PromiseInterface
    {
        $this->cacheItem = $this->cache->getItem($this->cacheKey);
        if ($this->cacheItem->isHit()) {
            return new FulfilledPromise($this->cacheItem->get());
        }
        try {
            $requestPromises = [];
            foreach ($this->requestUrls as $requestKey => $requestUrl) {
                $requestPromises[$requestKey] = $this->client->requestAsync('GET', $requestUrl, $this->guzzleOptions);
            }
        } catch (GuzzleException $e) {
            return $this->handleGuzzleException($e);
        }

        $aggregatePromise = \GuzzleHttp\Promise\all($requestPromises);
        $aggregatePromise = $aggregatePromise->then(
            // onSuccess
            Closure::fromCallable([$this, 'handleResponses']),
            // onError
            Closure::fromCallable([$this, 'handleAsyncError'])
        );
        return $aggregatePromise;
    }

    /**
     * @param Response[] $responses
     * @return mixed
     */
    private function handleResponses(array $responses)
    {
        try {
            $args = $this->parseResponseArguments;
            array_unshift($args, $responses);
            $result = call_user_func_array($this->parseResponseCallable, $args);
        } catch (MultiParseException $e) {
            $url = $this->requestUrls[$e->getResponseKey()] ?? $this->requestUrl;
            $this->logger->error('Error parsing feed for "{0}". Error was: {1}', [$url, $e->getMessage()]);
            return $this->nullResult;
        } catch (ParseException $e) {
            $this->logger->error('Error parsing feed for "{0}". Error was: {1}', [$this->requestUrl, $e->getMessage()]);
            return $this->nullResult;
        }
        $this->cache->setItem($this->cacheItem, $result, $this->standardTTL);
        return $result;
    }
}
