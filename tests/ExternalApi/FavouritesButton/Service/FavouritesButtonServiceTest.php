<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\FavouritesButton\Service;

use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\FavouritesButton\Domain\FavouritesButton;
use App\ExternalApi\FavouritesButton\Mapper\FavouritesButtonMapper;
use App\ExternalApi\FavouritesButton\Service\FavouritesButtonService;
use GuzzleHttp\Psr7\Response;
use Monolog\Logger;
use Tests\App\ExternalApi\BaseServiceTestCase;

class FavouritesButtonServiceTest extends BaseServiceTestCase
{
    public function setUp()
    {
        $this->setUpCache();
        $this->setUpLogger();
    }

    public function testGetContent()
    {
        $history = [];
        $service = $this->service(
            $this->client([$this->mockValidResponse()], $history)
        );

        $result = $service->getContent();

        $this->assertEquals(
            'https://api.example.com/test',
            $this->getLastRequestUrl($history)
        );

        $this->assertInstanceOf(FavouritesButton::class, $result);
        $this->assertEquals('<div></div>', $result->getHead());
        $this->assertEquals('if (this) { that(); }', $result->getScript());
        $this->assertEquals('<div></div>', $result->getBodyLast());

        // Ensure multiple calls use the cache instead of making multiple requests
        $service->getContent();
        $this->assertCount(1, $history);
    }

    public function testInvalidResponseIsHandledAndNotCached()
    {
        $service = $this->service(
            $this->client([new Response(200, [], '{"itemssssss": []}')])
        );

        $result = $service->getContent();

        // Assert empty result is returned
        $this->assertNull($result);

        // Assert nothing was saved in the cache
        $this->assertEmpty($this->validCacheValues());

        // Assert the error was logged
        $this->assertTrue($this->getLoggerHandler()->hasRecordThatMatches(
            '/Response must contain head, script and bodyLast elements/',
            Logger::ERROR
        ));
    }

    public function test500ErrorsAreHandledAndNotCached()
    {
        $service = $this->service(
            $this->client([new Response(500, [], '')])
        );

        $result = $service->getContent();

        // Assert empty result is returned
        $this->assertNull($result);

        // Assert nothing was saved in the cache
        $this->assertEmpty($this->validCacheValues());
    }

    public function test404ErrorsAreHandledAndNotCached()
    {
        $service = $this->service(
            $this->client([new Response(404, [], '')])
        );

        $result = $service->getContent();

        // Assert empty result is returned
        $this->assertNull($result);

        // Assert nothing was saved in the cache
        $this->assertEmpty($this->validCacheValues());
    }

    private function mockValidResponse(): Response
    {
        $body = json_encode(
            [
                'head' => ' <div></div>',
                'script' => 'if (this) { that(); } ',
                'bodyLast' => ' <div></div> ',
            ]
        );

        return new Response(200, [], $body);
    }

    private function service($client)
    {
        return new FavouritesButtonService(
            new HttpApiClientFactory($client, $this->cache, $this->logger),
            new FavouritesButtonMapper(),
            'https://api.example.com/test'
        );
    }
}
