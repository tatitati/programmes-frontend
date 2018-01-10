<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Client;

use App\ExternalApi\Client\HttpApiClient;
use BBC\ProgrammesPagesService\Cache\CacheInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerInterface;
use Tests\App\ExternalApi\HttpApiTestBase;

/**
 * The majority of the functionality of this class is tested in the *ServiceTest tests, this is quite basic
 */
class HttpApiClientTest extends HttpApiTestBase
{
    private $mockCache;

    private $mockLogger;

    public function setUp()
    {
        $this->mockCache = $this->createMock(CacheInterface::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);
    }

    public function testMakeCachedRequest()
    {
        $guzzleClient = $this->createMock(ClientInterface::class);

        $cacheKey = "cachekey";
        $mockCacheItem = $this->createMock(CacheItemInterface::class);

        $mockCacheItem->expects($this->once())
            ->method('isHit')
            ->willReturn(true);

        $mockCacheItem->expects($this->once())
            ->method('get')
            ->willReturn('A suffusion of yellow');

        $this->mockCache->expects($this->once())
            ->method('getItem')
            ->with($cacheKey)
            ->willReturn($mockCacheItem);

        $httpApiClient = $this->createHttpApiClient(
            $guzzleClient,
            $cacheKey,
            'http://www.api.com',
            function () {
                // no-op
            }
        );
        $result = $httpApiClient->makeCachedRequest();
        $this->assertEquals('A suffusion of yellow', $result);
    }

    public function testMakeCachedPromise()
    {
        $guzzleClient = $this->createMock(ClientInterface::class);

        $cacheKey = "cachekey";
        $mockCacheItem = $this->createMock(CacheItemInterface::class);

        $mockCacheItem->expects($this->once())
            ->method('isHit')
            ->willReturn(true);

        $mockCacheItem->expects($this->once())
            ->method('get')
            ->willReturn('A suffusion of yellow');

        $this->mockCache->expects($this->once())
            ->method('getItem')
            ->with($cacheKey)
            ->willReturn($mockCacheItem);

        $httpApiClient = $this->createHttpApiClient(
            $guzzleClient,
            $cacheKey,
            'http://www.api.com',
            function () {
                // no-op
            }
        );
        $promise = $httpApiClient->makeCachedPromise();
        $this->assertEquals('A suffusion of yellow', $promise->wait(true));
    }

    public function testMakeUncachedRequest()
    {
        $response = new Response(200, [], 'The correct response');
        $guzzleClient = $this->makeGuzzleClientToRespondWith($response);

        $mockCacheItem = $this->createMock(CacheItemInterface::class);
        $mockCacheItem->expects($this->once())
            ->method('isHit')
            ->willReturn(false);

        $this->mockCache->expects($this->once())
            ->method('getItem')
            ->willReturn($mockCacheItem);

        $this->mockCache->expects($this->once())
            ->method('setItem')
            ->with($mockCacheItem, 'The parsed response', CacheInterface::X_LONG);

        $httpApiClient = $this->createHttpApiClient(
            $guzzleClient,
            'cachekey',
            'http://www.api.com',
            function (Response $response, $arg1) {
                $body = $response->getBody()->getContents();
                if ($body === 'The correct response') {
                    return $arg1;
                }
            },
            ['The parsed response']
        );
        $this->assertEquals('The parsed response', $httpApiClient->makeCachedRequest());
    }

    public function testMakeUncachedPromise()
    {
        $response = new Response(200, [], 'The correct response');
        $guzzleClient = $this->makeGuzzleClientToRespondWith($response);

        $mockCacheItem = $this->createMock(CacheItemInterface::class);
        $mockCacheItem->expects($this->once())
            ->method('isHit')
            ->willReturn(false);

        $this->mockCache->expects($this->once())
            ->method('getItem')
            ->willReturn($mockCacheItem);

        $this->mockCache->expects($this->once())
            ->method('setItem')
            ->with($mockCacheItem, 'The parsed response', CacheInterface::X_LONG);

        $httpApiClient = $this->createHttpApiClient(
            $guzzleClient,
            'cachekey',
            'http://www.api.com',
            function (Response $response) {
                $body = $response->getBody()->getContents();
                if ($body === 'The correct response') {
                    return 'The parsed response';
                }
            }
        );
        $promise = $httpApiClient->makeCachedPromise();
        $this->assertEquals('The parsed response', $promise->wait(true));
    }

    private function createHttpApiClient(
        ClientInterface $guzzleClient,
        string $cacheKey,
        string $requestUrl,
        callable $parseResponse,
        array $parseResponseArgs = [],
        $nullResult = [],
        $standardCache = CacheInterface::X_LONG,
        $notFoundCache = CacheInterface::SHORT
    ) {
        return new HttpApiClient(
            $guzzleClient,
            $this->mockCache,
            $this->mockLogger,
            $cacheKey,
            $requestUrl,
            $parseResponse,
            $parseResponseArgs,
            $nullResult,
            $standardCache,
            $notFoundCache
        );
    }
}
