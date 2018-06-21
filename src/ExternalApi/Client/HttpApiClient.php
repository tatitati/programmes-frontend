<?php
declare(strict_types = 1);

namespace App\ExternalApi\Client;

use App\ExternalApi\Exception\ParseException;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use Closure;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class HttpApiClient
{
    /** @var ClientInterface */
    protected $client;

    /** @var CacheInterface */
    protected $cache;

    /** @var LoggerInterface */
    protected $logger;

    /** @var string */
    protected $cacheKey;

    /** @var string */
    protected $requestUrl;

    /** @var callable */
    protected $parseResponseCallable;

    /** @var array */
    protected $parseResponseArguments;

    /** @var mixed */
    protected $nullResult;

    /** @var int|string */
    protected $standardTTL;

    /** @var int|string */
    protected $notFoundTTL;

    /** @var CacheItemInterface */
    protected $cacheItem;

    /** @var array */
    protected $guzzleOptions;

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
     * @param array $guzzleOptions
     *   Extra options for guzzle client
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
        $notFoundTTL,
        array $guzzleOptions
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
        $this->guzzleOptions = $guzzleOptions;
    }

    public function makeCachedRequest()
    {
        $this->cacheItem = $this->cache->getItem($this->cacheKey);
        if ($this->cacheItem->isHit()) {
            return $this->cacheItem->get();
        }
        try {
            $response = $this->client->request('GET', $this->requestUrl, $this->guzzleOptions);
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
            $requestPromise = $this->client->requestAsync('GET', $this->requestUrl, $this->guzzleOptions);
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

    protected function handleResponse(Response $response)
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

    protected function handleGuzzleException(GuzzleException $e)
    {
        $responseCode = "Unknown";
        if ($e instanceof RequestException && $e->getResponse() && $e->getResponse()->getStatusCode()) {
            $responseCode = $e->getResponse()->getStatusCode();
        }
        if ($responseCode != 404) {
            $url = $this->requestUrl;
            if ($e instanceof RequestException) {
                $url = (string) $e->getRequest()->getUri();
            }
            $this->logger->error("HTTP Error status $responseCode for $url : " . $e->getMessage());
        } elseif ($this->notFoundTTL !== CacheInterface::NONE) {
            // 404s get cached for a shorter time
            $this->cache->setItem($this->cacheItem, $this->nullResult, $this->notFoundTTL);
        }
        return $this->nullResult;
    }

    protected function handleAsyncError($reason)
    {
        if ($reason instanceof GuzzleException) {
            return $this->handleGuzzleException($reason);
        }
        if ($reason instanceof Throwable) {
            throw $reason;
        }
        $this->logger->error("An unknown issue occurred handling a guzzle error whose reason was not an exception. Probable URL: $this->requestUrl");
        return $this->nullResult;
    }
}
