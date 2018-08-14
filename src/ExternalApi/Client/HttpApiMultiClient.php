<?php
declare(strict_types = 1);

namespace App\ExternalApi\Client;

use App\ExternalApi\Exception\MultiParseException;
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

class HttpApiMultiClient
{
    /** @var ClientInterface */
    private $client;

    /** @var CacheInterface */
    private $cache;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $cacheKey;

    /** @var callable */
    private $parseResponseCallable;

    /** @var array */
    private $parseResponseArguments;

    /** @var mixed */
    private $resultOnError;

    /** @var int|string */
    private $standardTTL;

    /** @var int|string */
    private $notFoundTTL;

    /** @var CacheItemInterface */
    private $cacheItem;

    /** @var array */
    private $guzzleOptions;

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
     * @param mixed $resultOnError
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
        $resultOnError,
        $standardTTL,
        $notFoundTTL,
        array $guzzleOptions
    ) {
        $this->requestUrls = $requestUrls;
        $this->client = $client;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->cacheKey = $cacheKey;
        $this->parseResponseCallable = $parseResponse;
        $this->parseResponseArguments = $parseResponseArguments;
        $this->resultOnError = $resultOnError;
        $this->standardTTL = $standardTTL;
        $this->notFoundTTL = $notFoundTTL;
        $this->guzzleOptions = $guzzleOptions;
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
            $nullResponse = $this->handleGuzzleException($e);
            return new FulfilledPromise($nullResponse);
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
            $url = $this->requestUrls[$e->getResponseKey()] ?? reset($this->requestUrls);
            $this->logger->error('Error parsing feed for "{0}". Error was: {1}', [$url, $e->getMessage()]);
            return $this->resultOnError;
        } catch (ParseException $e) {
            $this->logger->error('Error parsing feed for one of this URLs: "{0}". Error was: {1}', [implode(',', $this->requestUrls), $e->getMessage()]);
            return $this->resultOnError;
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
            if ($e instanceof RequestException) {
                $urls = (string) $e->getRequest()->getUri();
            } else {
                $urls = implode(',', $this->requestUrls);
            }
            $this->logger->error("HTTP Error status $responseCode for one of this URLs $urls : " . $e->getMessage());
        } elseif ($this->notFoundTTL !== CacheInterface::NONE) {
            // 404s get cached for a shorter time
            $this->cache->setItem($this->cacheItem, $this->resultOnError, $this->notFoundTTL);
        }
        return $this->resultOnError;
    }

    private function handleAsyncError($reason)
    {
        if ($reason instanceof GuzzleException) {
            return $this->handleGuzzleException($reason);
        }
        if ($reason instanceof Throwable) {
            throw $reason;
        }
        $this->logger->error("An unknown issue occurred handling a guzzle error whose reason was not an exception. Probable URL: " . reset($this->requestUrls));
        return $this->resultOnError;
    }
}
