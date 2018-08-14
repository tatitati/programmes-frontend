<?php
declare(strict_types = 1);

namespace App\ExternalApi\Client;

use BBC\ProgrammesCachingLibrary\CacheInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

class HttpApiClientFactory
{
    private const DEFAULT_GUZZLE_OPTIONS = ['timeout' => 6];
    /** @var ClientInterface */
    private $client;

    /** @var CacheInterface */
    private $cache;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ClientInterface $client,
        CacheInterface $cache,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function getHttpApiMultiClient(
        string $cacheKey,
        array $requestUrls,
        callable $parseResponse,
        array $parseResponseArguments = [],
        $resultOnError = [],
        $standardTTL = CacheInterface::MEDIUM,
        $notFoundTTL = CacheInterface::NORMAL,
        array $guzzleOptions = []
    ) {
        $guzzleOptions = array_merge(self::DEFAULT_GUZZLE_OPTIONS, $guzzleOptions);
        return new HttpApiMultiClient(
            $this->client,
            $this->cache,
            $this->logger,
            $cacheKey,
            $requestUrls,
            $parseResponse,
            $parseResponseArguments,
            $resultOnError,
            $standardTTL,
            $notFoundTTL,
            $guzzleOptions
        );
    }

    public function keyHelper(string $className, string $functionName, ...$uniqueValues): string
    {
        return $this->cache->keyHelper($className, $functionName, ...$uniqueValues);
    }
}
