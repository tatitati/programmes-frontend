<?php
declare(strict_types = 1);

namespace App\ExternalApi\Client;

use BBC\ProgrammesCachingLibrary\CacheInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Exception\GuzzleException;
use App\ExternalApi\Exception\ParseException;
use GuzzleHttp\Psr7\Response;
use Closure;
use Throwable;

class HttpApiClient
{
    /** @var ClientInterface */
    private $client;

    /** @var CacheInterface */
    private $cache;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $cacheKey;

    /** @var string */
    private $requestUrl;

    /** @var callable */
    private $parseResponseCallable;

    /** @var array */
    private $parseResponseArguments;

    /** @var mixed */
    private $nullResult;

    /** @var int|string */
    private $standardTTL;

    /** @var int|string */
    private $notFoundTTL;

    /** @var CacheItemInterface */
    private $cacheItem;

    /**
     * @param ClientInterface $client
     * @param CacheInterface $cache
     * @param LoggerInterface $logger
     * @param string $cacheKey
     *   pre-generated cacheKey. Use HttpApiClientFactory->keyHelper()
     * @param string $requestUrl
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
     */
    public function __construct(
        ClientInterface $client,
        CacheInterface $cache,
        LoggerInterface $logger,
        string $cacheKey,
        string $requestUrl,
        callable $parseResponse,
        array $parseResponseArguments,
        $nullResult,
        $standardTTL,
        $notFoundTTL
    ) {
        $this->client = $client;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->cacheKey = $cacheKey;
        $this->requestUrl = $requestUrl;
        $this->parseResponseCallable = $parseResponse;
        $this->parseResponseArguments = $parseResponseArguments;
        $this->nullResult = $nullResult;
        $this->standardTTL = $standardTTL;
        $this->notFoundTTL = $notFoundTTL;
    }

    public function makeCachedRequest()
    {
        $this->cacheItem = $this->cache->getItem($this->cacheKey);
        if ($this->cacheItem->isHit()) {
            return $this->cacheItem->get();
        }
        try {
            $response = $this->client->request('GET', $this->requestUrl);
        } catch (GuzzleException $e) {
            return $this->handleGuzzleException($e);
        }
        return $this->handleResponse($response);
    }

    public function makeCachedPromise(): PromiseInterface
    {
        $this->cacheItem = $this->cache->getItem($this->cacheKey);
        if ($this->cacheItem->isHit()) {
            return new FulfilledPromise($this->cacheItem->get());
        }
        try {
            $requestPromise = $this->client->requestAsync('GET', $this->requestUrl);
        } catch (GuzzleException $e) {
            return $this->handleGuzzleException($e);
        }

        $requestPromise = $requestPromise->then(
            // onSuccess
            Closure::fromCallable([$this, 'handleResponse']),
            // onError
            Closure::fromCallable([$this, 'handleAsyncError'])
        );
        return $requestPromise;
    }

    private function handleResponse(Response $response)
    {
        try {
            $args = $this->parseResponseArguments;
            array_unshift($args, $response);
            $result = call_user_func_array($this->parseResponseCallable, $args);
        } catch (ParseException $e) {
            $this->logger->error('Error parsing feed for "{0}". Error was: {1}', [$this->requestUrl, $e->getMessage()]);
            return $this->nullResult;
        }
        $this->cache->setItem($this->cacheItem, $result, $this->standardTTL);
        return $result;
    }

    private function handleGuzzleException(GuzzleException $e)
    {
        $responseCode = "Unknown";
        if ($e instanceof RequestException && $e->getResponse() && $e->getResponse()->getStatusCode()) {
            $responseCode = $e->getResponse()->getStatusCode();
        }

        if ($responseCode != 404) {
            $this->logger->error("HTTP Error status $responseCode for $this->requestUrl : " . $e->getMessage());
        } elseif ($this->notFoundTTL !== CacheInterface::NONE) {
            // 404s get cached for a shorter time
            $this->cache->setItem($this->cacheItem, $this->nullResult, $this->notFoundTTL);
        }
        return $this->nullResult;
    }

    private function handleAsyncError($reason)
    {
        if ($reason instanceof GuzzleException) {
            return $this->handleGuzzleException($reason);
        }
        if ($reason instanceof Throwable) {
            throw $reason;
        }
        $this->logger->error("An unknown issue occurred handling a guzzle error whose reason was not an exception. URL: $this->requestUrl");
        return $this->nullResult;
    }
}
