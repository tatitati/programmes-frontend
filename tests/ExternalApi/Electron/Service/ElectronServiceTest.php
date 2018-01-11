<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Electron\Service;

use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\Electron\Service\ElectronService;
use App\ExternalApi\Electron\Domain\SupportingContentItem;
use App\ExternalApi\Electron\Mapper\SupportingContentMapper;
use App\ExternalApi\XmlParser\XmlParser;
use BBC\ProgrammesPagesService\Cache\CacheInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use GuzzleHttp\Psr7\Response;
use Monolog\Logger;
use Tests\App\ExternalApi\BaseServiceTestCase;

class ElectronServiceTest extends BaseServiceTestCase
{
    public function setUp()
    {
        $this->setUpCache();
        $this->setUpLogger();
    }

    public function testFetchSupportingContentItemsForProgrammeEndToEnd()
    {
        $history = [];
        $xml = file_get_contents(dirname(dirname(__DIR__)) . '/XmlParser/electron_eastenders.xml');
        $service = $this->service(
            $this->client([new Response(200, [], $xml)], $history)
        );
        $programme = $this->createConfiguredMock(Brand::class, ['getPid' => new Pid('b006m86d')]);
        $items = $service->fetchSupportingContentItemsForProgramme($programme);
        $this->assertCount(2, $items);
        $this->assertEquals('The Queen Vic Jukebox on BBC Music', $items[0]->getTitle());

        // Ensure multiple calls use the cache instead of making multiple requests
        $service->fetchSupportingContentItemsForProgramme($programme);
        $this->assertCount(1, $history);
    }


    public function testExceptionsAreHandled()
    {
        $service = $this->service(
            $this->client([new Response(500, [], '')])
        );

        $programme = $this->createConfiguredMock(Brand::class, ['getPid' => new Pid('b006m86d')]);
        $result = $service->fetchSupportingContentItemsForProgramme($programme);

        // Assert empty result is returned
        $this->assertEquals([], $result);

        // Assert nothing was saved in the cache
        $this->assertEmpty($this->validCacheValues());
    }

    public function testInvalidDataIsHandled()
    {
        $xml = file_get_contents(dirname(dirname(__DIR__)) . '/XmlParser/invalid.xml');
        $service = $this->service(
            $this->client([ new Response(200, [], $xml)])
        );

        $programme = $this->createConfiguredMock(Brand::class, ['getPid' => new Pid('b006m86d')]);
        $result = $service->fetchSupportingContentItemsForProgramme($programme);

         // Assert empty result is returned
        $this->assertEquals([], $result);

        // Assert nothing was saved in the cache
        $this->assertEmpty($this->validCacheValues());

        // Assert the error was logged
        $this->assertTrue($this->getLoggerHandler()->hasRecordThatMatches(
            '/Error parsing feed for "https:\/\/.*"\. Error was: Unable to parse XML/',
            Logger::ERROR
        ));
    }

    public function test404sAreCached()
    {
        $service = $this->service(
            $this->client([new Response(404, [], '')])
        );

        $programme = $this->createConfiguredMock(Brand::class, ['getPid' => new Pid('b006m86d')]);
        $result = $service->fetchSupportingContentItemsForProgramme($programme);

        // Assert empty result is returned
        $this->assertEquals([], $result);

        // Assert an empty result was stored in the cache
        $validCacheValues = $this->validCacheValues();
        $this->assertCount(1, $validCacheValues);
        $this->assertContains([], $validCacheValues);
    }

    private function service($client): ElectronService
    {
        // Okay. So this is more of an integration test than a unit test at this point.
        // We're testing both ElectronService and HttpApiClient. This does not seem unreasonable to me however.
        return new ElectronService(
            new HttpApiClientFactory($client, $this->cache, $this->logger),
            new XmlParser(),
            new SupportingContentMapper(),
            'https://api.example.com'
        );
    }
}
