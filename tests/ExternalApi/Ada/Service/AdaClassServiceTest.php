<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Ada\Service;

use App\ExternalApi\Ada\Domain\AdaClass;
use App\ExternalApi\Ada\Mapper\AdaClassMapper;
use App\ExternalApi\Ada\Service\AdaClassService;
use App\ExternalApi\Client\HttpApiClientFactory;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use GuzzleHttp\Psr7\Response;
use Monolog\Logger;
use Tests\App\ExternalApi\BaseServiceTestCase;

class AdaClassServiceTest extends BaseServiceTestCase
{
    public function setUp()
    {
        $this->setUpCache();
        $this->setUpLogger();
    }

    public function testFindRelatedClassesByContainer()
    {
        $history = [];
        $service = $this->service(
            $this->client([$this->mockValidResponse()], $history)
        );

        $result = $service->findRelatedClassesByContainer($this->mockProgramme())->wait(true);

        $this->assertEquals(
            'https://api.example.com/test/classes?page=1&page_size=10&programme=b0000001&count_context=b0000001&threshold=2&order=rank&direction=descending',
            $this->getLastRequestUrl($history)
        );

        $this->assertContainsOnly(AdaClass::class, $result);
        $this->assertCount(2, $result);
        $this->assertEquals('Fellows_of_the_Royal_Society', $result[0]->getId());
        $this->assertEquals('Concepts_in_physics', $result[1]->getId());

        // Ensure multiple calls use the cache instead of making multiple requests
        $service->findRelatedClassesByContainer($this->mockProgramme());
        $this->assertCount(1, $history);
    }

    public function testFindRelatedClassesByContainerWithoutCountContext()
    {
        $history = [];
        $service = $this->service(
            $this->client([$this->mockValidResponse()], $history)
        );

        $result = $service->findRelatedClassesByContainer($this->mockProgramme(), false)->wait(true);

        $this->assertEquals(
            'https://api.example.com/test/classes?page=1&page_size=10&programme=b0000001&threshold=2&order=rank&direction=descending',
            $this->getLastRequestUrl($history)
        );
    }

    public function testInvalidResponseIsHandledAndNotCached()
    {
        $service = $this->service(
            $this->client([new Response(200, [], '{"itemssssss": []}')])
        );

        $result = $service->findRelatedClassesByContainer($this->mockProgramme())->wait(true);

        // Assert empty result is returned
        $this->assertEquals([], $result);

        // Assert nothing was saved in the cache
        $this->assertEmpty($this->validCacheValues());

        // Assert the error was logged
        $this->assertTrue($this->getLoggerHandler()->hasRecordThatMatches(
            '/Error parsing feed for "https:\/\/.*"\. Error was: Ada JSON response does not contain items element/',
            Logger::ERROR
        ));
    }

    public function test500ErrorsAreHandledAndNotCached()
    {
        $service = $this->service(
            $this->client([new Response(500, [], '')])
        );

        $result = $service->findRelatedClassesByContainer($this->mockProgramme())->wait(true);

        // Assert empty result is returned
        $this->assertEquals([], $result);

        // Assert nothing was saved in the cache
        $this->assertEmpty($this->validCacheValues());
    }

    public function test404ErrorsAreHandledAndCached()
    {
        $service = $this->service(
            $this->client([new Response(404, [], '')])
        );

        $result = $service->findRelatedClassesByContainer($this->mockProgramme())->wait(true);

        // Assert empty result is returned
        $this->assertEquals([], $result);

        // Assert an empty result was stored in the cache
        $validCacheValues = $this->validCacheValues();
        $this->assertCount(1, $validCacheValues);
        $this->assertContains([], $validCacheValues);
    }

    private function service($client)
    {
        return new AdaClassService(
            new HttpApiClientFactory($client, $this->cache, $this->logger),
            'https://api.example.com/test',
            new AdaClassMapper()
        );
    }

    private function mockProgramme(): Programme
    {
        $mockProgramme = $this->createMock(Programme::class);
        $mockProgramme->method('getTleo')->will($this->returnSelf());
        $mockProgramme->method('getPid')->willReturn(new Pid('b0000001'));

        return $mockProgramme;
    }

    private function mockValidResponse(): Response
    {
        $body = <<<'BODY'
{
    "page": 1,
    "page_size": 10,
    "items": [
        {
            "id": "Fellows_of_the_Royal_Society",
            "type": "category",
            "title": "Fellows of the Royal Society",
            "image": "ichef.bbci.co.uk\/images\/ic\/$recipe\/p039xhcv.jpg",
            "programme_items_count": 16
        },
        {
            "id": "Concepts_in_physics",
            "type": "category",
            "title": "Concepts in physics",
            "image": "ichef.bbci.co.uk\/images\/ic\/$recipe\/p054zzwj.jpg",
            "programme_items_count": 13
        }
    ]
}
BODY;

        return new Response(200, [], $body);
    }
}
