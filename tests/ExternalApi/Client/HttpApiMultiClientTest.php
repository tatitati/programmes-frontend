<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Client;

use App\ExternalApi\Client\HttpApiMultiClient;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerInterface;

/**
 * The majority of the functionality of this class is tested in the *ServiceTest tests, this is quite basic
 */
class HttpApiMultiClientTest extends TestCase
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

        $httpApiClient = $this->createHttpApiMultiClient(
            $guzzleClient,
            $cacheKey,
            ['http://www.api.com/a', 'http://www.api.com/b'],
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

        $httpApiClient = $this->createHttpApiMultiClient(
            $guzzleClient,
            $cacheKey,
            ['http://www.api.com/a', 'http://www.api.com/b'],
            function () {
                // no-op
            }
        );
        $promise = $httpApiClient->makeCachedPromise();
        $this->assertEquals('A suffusion of yellow', $promise->wait(true));
    }

    public function testMakeUncachedRequest()
    {
        $response1 = new Response(200, [], 'The correct response');
        $response2 = new Response(200, [], 'The technically correct response, the best kind of response');
        $guzzleClient = $this->makeGuzzleClientToRespondWith([$response1, $response2]);

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

        $httpApiClient = $this->createHttpApiMultiClient(
            $guzzleClient,
            'cachekey',
            ['http://www.api.com/a', 'http://www.api.com/a'],
            function (array $responses, $arg1) {
                $body1 = $responses[0]->getBody()->getContents();
                $body2 = $responses[1]->getBody()->getContents();
                if ($body1 === 'The correct response' && $body2 === 'The technically correct response, the best kind of response') {
                    return $arg1;
                }
            },
            ['The parsed response']
        );
        $this->assertEquals('The parsed response', $httpApiClient->makeCachedRequest());
    }

    public function testMakeUncachedPromise()
    {
        $response1 = new Response(200, [], 'The correct response');
        $response2 = new Response(200, [], 'The technically correct response, the best kind of response');
        $guzzleClient = $this->makeGuzzleClientToRespondWith([$response1, $response2]);

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

        $httpApiClient = $this->createHttpApiMultiClient(
            $guzzleClient,
            'cachekey',
            ['http://www.api.com/a', 'http://www.api.com/a'],
            function (array $responses, $arg1) {
                $body1 = $responses[0]->getBody()->getContents();
                $body2 = $responses[1]->getBody()->getContents();
                if ($body1 === 'The correct response' && $body2 === 'The technically correct response, the best kind of response') {
                    return $arg1;
                }
            },
            ['The parsed response']
        );
        $this->assertEquals('The parsed response', $httpApiClient->makeCachedPromise()->wait(true));
    }

    public function testErrorLoggingAndNotCaching()
    {
        $response1 = new Response(200, [], 'The correct response');
        $response2 = new Response(500, [], 'Oops');
        $guzzleClient = $this->makeGuzzleClientToRespondWith([$response1, $response2]);

        $mockCacheItem = $this->createMock(CacheItemInterface::class);
        $mockCacheItem->expects($this->once())
            ->method('isHit')
            ->willReturn(false);

        $this->mockCache->expects($this->once())
            ->method('getItem')
            ->willReturn($mockCacheItem);

        $this->mockCache->expects($this->never())
            ->method('setItem');

        $httpApiClient = $this->createHttpApiMultiClient(
            $guzzleClient,
            'cachekey',
            ['http://www.api.com/a', 'http://www.api.com/fail'],
            function (array $responses, $arg1) {
                // doesnt matter
            }
        );
        $this->mockLogger->expects($this->once())
            ->method('error')
            ->with($this->matchesRegularExpression('/HTTP Error status 500 for http:\/\/www\.api\.com\/fail : /'));

        $this->assertEquals([], $httpApiClient->makeCachedPromise()->wait(true));
    }

    private function createHttpApiMultiClient(
        ClientInterface $guzzleClient,
        string $cacheKey,
        array $requestUrls,
        callable $parseResponse,
        array $parseResponseArgs = [],
        $nullResult = [],
        $standardCache = CacheInterface::X_LONG,
        $notFoundCache = CacheInterface::SHORT
    ) {
        return new HttpApiMultiClient(
            $guzzleClient,
            $this->mockCache,
            $this->mockLogger,
            $cacheKey,
            $requestUrls,
            $parseResponse,
            $parseResponseArgs,
            $nullResult,
            $standardCache,
            $notFoundCache,
            ['timeout' => 10]
        );
    }

    private function makeGuzzleClientToRespondWith(array $responses): Client
    {
        $stack = MockHandler::createWithMiddleware($responses);
        $client = new Client(['handler' => $stack]);
        return $client;
    }
}
