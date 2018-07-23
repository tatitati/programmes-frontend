<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Client;

use App\ExternalApi\Client\HttpApiMultiClient;
use App\ExternalApi\Exception\MultiParseException;
use App\ExternalApi\Exception\ParseException;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
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

    public function testMakeUncachedPromiseExceptionReturnsNullResult()
    {
        $guzzleClient = $this->createMock(Client::class);
        $guzzleClient->expects($this->any())
            ->method('requestAsync')
            ->willThrowException(new RequestException('test', new Request('get', 'http://www.api.com/fail')));

        $httpApiClient = $this->createHttpApiMultiClient(
            $guzzleClient,
            'cachekey',
            ['http://www.api.com/a', 'http://www.api.com/a'],
            function () {
                // no-op
            },
            [],
            ['data' => ''] // null result
        );

        // Assert the error was logged
        $this->mockLogger->expects($this->once())
            ->method('error')
            ->with($this->matchesRegularExpression('/HTTP Error status Unknown for one of this URLs http:\/\/www\.api\.com\/fail : /'));
        // Assert $nullResponse is return when a GuzzleException is thrown
         $this->assertEquals(['data' => ''], $httpApiClient->makeCachedPromise()->wait(true));
    }

    /**
     * Test when the callback for parsing the results throws an MultiParseException the error is logged and null result returned
     */
    public function testParseResponseCallableThrowsMultiParseException()
    {
        $guzzleClient = $this->createMock(Client::class);
        $httpApiClient = $this->createHttpApiMultiClient(
            $guzzleClient,
            'cachekey',
            ['http://www.api.com/a', 'http://www.api.com/b'],
            function () {
                throw new MultiParseException(0, 'Error when parsing the responses');
            },
            [],
            ['data' => ''] // null result
        );
        // Assert the error was logged
        $this->mockLogger->expects($this->once())
            ->method('error')
            ->with('Error parsing feed for "{0}". Error was: {1}', ['http://www.api.com/a', 'Error when parsing the responses']);
        // Assert $nullResponse is return when a GuzzleException is thrown
        $this->assertEquals(['data' => ''], $httpApiClient->makeCachedPromise()->wait(true));
    }

    /**
     * Same as testParseResponseCallableThrowsMultiParseException but for ParseException
     */
    public function testParseResponseCallableThrowsParseException()
    {
        $guzzleClient = $this->createMock(Client::class);
        $httpApiClient = $this->createHttpApiMultiClient(
            $guzzleClient,
            'cachekey',
            ['http://www.api.com/a', 'http://www.api.com/b'],
            function () {
                throw new ParseException('Error when parsing a response');
            },
            [],
            ['data' => ''] // null result
        );
        // Assert the error was logged
        $this->mockLogger->expects($this->once())
            ->method('error')
            ->with('Error parsing feed for one of this URLs: "{0}". Error was: {1}', ['http://www.api.com/a,http://www.api.com/b', 'Error when parsing a response']);
        // Assert $nullResponse is return when a GuzzleException is thrown
        $this->assertEquals(['data' => ''], $httpApiClient->makeCachedPromise()->wait(true));
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
            ->with($this->matchesRegularExpression('/HTTP Error status 500 for one of this URLs http:\/\/www\.api\.com\/fail : /'));

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
