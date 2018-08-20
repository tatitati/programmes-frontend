<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\SoundsNav\Service;

use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\SoundsNav\Domain\SoundsNav;
use App\ExternalApi\SoundsNav\Mapper\SoundsNavMapper;
use App\ExternalApi\SoundsNav\Service\SoundsNavService;
use GuzzleHttp\Psr7\Response;
use Monolog\Logger;
use Tests\App\ExternalApi\BaseServiceTestCase;

class SoundsNavServiceTest extends BaseServiceTestCase
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

        $result = $service->getContent()->wait(true);

        $this->assertEquals(
            'https://api.example.com/test',
            $this->getLastRequestUrl($history)
        );

        $this->assertInstanceOf(SoundsNav::class, $result);
        $this->assertEquals('<div></div>', $result->getHead());
        $this->assertEquals('if (this) { that(); }', $result->getBody());
        $this->assertEquals('<div></div>', $result->getFoot());

        // Ensure multiple calls use the cache instead of making multiple requests
        $service->getContent()->wait(true);
        $this->assertCount(1, $history);
    }

    public function testInvalidResponseIsHandledAndNotCached()
    {
        $service = $this->service(
            $this->client([new Response(200, [], '{"itemssssss": []}')])
        );

        $result = $service->getContent()->wait(true);

        // Assert empty result is returned
        $this->assertNull($result);

        // Assert nothing was saved in the cache
        $this->assertEmpty($this->validCacheValues());

        // Assert the error was logged
        $this->assertTrue($this->getLoggerHandler()->hasRecordThatMatches(
            '/Response must contain head, body and foot elements/',
            Logger::ERROR
        ));
    }

    public function test500ErrorsAreHandledAndNotCached()
    {
        $service = $this->service(
            $this->client([new Response(500, [], '')])
        );

        $result = $service->getContent()->wait(true);

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

        $result = $service->getContent()->wait(true);

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
                'body' => 'if (this) { that(); } ',
                'foot' => ' <div></div> ',
            ]
        );

        return new Response(200, [], $body);
    }

    private function service($client)
    {
        return new SoundsNavService(
            new HttpApiClientFactory($client, $this->cache, $this->logger),
            new SoundsNavMapper(),
            'https://api.example.com/test'
        );
    }
}
