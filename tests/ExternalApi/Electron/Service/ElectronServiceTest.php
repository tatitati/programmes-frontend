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
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerInterface;
use Tests\App\ExternalApi\HttpApiTestBase;

class ElectronServiceTest extends HttpApiTestBase
{
    private $mockCache;

    private $xmlParser;

    private $contentMapper;

    private $mockLogger;

    private $httpApiClientFactory;

    public function testFetchSupportingContentItemsForProgrammeEndToEnd()
    {
        $xml = file_get_contents(dirname(dirname(__DIR__)) . '/XmlParser/electron_eastenders.xml');
        $response = new Response(200, [], $xml);
        $client = $this->makeGuzzleClientToRespondWith($response);
        $electronService = $this->makeElectronService($client);
        $programme = $this->createMock(Brand::class);
        $programme->expects($this->atLeastOnce())->method('getPid')->willReturn(new Pid('b006m86d'));
        $items = $electronService->fetchSupportingContentItemsForProgramme($programme);
        $this->assertCount(2, $items);
        $this->assertEquals('The Queen Vic Jukebox on BBC Music', $items[0]->getTitle());
    }

    public function testItemRetrievedFromCache()
    {
        $client = $this->createMock(Client::class);
        $electronService = $this->makeElectronService($client);
        $this->mockCache->expects($this->atLeastOnce())
            ->method('keyHelper')
            ->willReturn('mockCacheKey');

        $expectedContent = [new SupportingContentItem('A title', '<blink>Lols</blink>', null)];
        $mockCacheItem = $this->createMock(CacheItemInterface::class);
        $mockCacheItem->expects($this->once())
            ->method('isHit')
            ->willReturn(true);
        $mockCacheItem->expects($this->once())
            ->method('get')
            ->willReturn($expectedContent);

        $this->mockCache->expects($this->once())
            ->method('getItem')
            ->willReturn($mockCacheItem);

        $programme = $this->createMock(Brand::class);
        $programme->expects($this->atLeastOnce())->method('getPid')->willReturn(new Pid('b006m86d'));
        $result = $electronService->fetchSupportingContentItemsForProgramme($programme);
        $this->assertEquals($expectedContent, $result);
    }

    public function testExceptionsAreHandled()
    {
        $response = new Response(500, [], 'An Error');
        $client = $this->makeGuzzleClientToRespondWith($response);
        $electronService = $this->makeElectronService($client);
        $programme = $this->createMock(Brand::class);
        $programme->expects($this->atLeastOnce())->method('getPid')->willReturn(new Pid('b006m86d'));
        $items = $electronService->fetchSupportingContentItemsForProgramme($programme);
        $this->assertEquals([], $items);
    }

    public function testInvalidDataIsHandled()
    {
        $xml = file_get_contents(dirname(dirname(__DIR__)) . '/XmlParser/invalid.xml');
        $response = new Response(200, [], $xml);
        $client = $this->makeGuzzleClientToRespondWith($response);
        $electronService = $this->makeElectronService($client);
        $programme = $this->createMock(Brand::class);
        $programme->expects($this->atLeastOnce())->method('getPid')->willReturn(new Pid('b006m86d'));
        $this->mockLogger->expects($this->once())->method('error');
        $items = $electronService->fetchSupportingContentItemsForProgramme($programme);
        $this->assertEquals([], $items);
    }

    public function test404sAreCached()
    {
        $response = new Response(404, [], 'An Error');
        $client = $this->makeGuzzleClientToRespondWith($response);
        $electronService = $this->makeElectronService($client);
        $programme = $this->createMock(Brand::class);
        $programme->expects($this->atLeastOnce())->method('getPid')->willReturn(new Pid('b006m86d'));

        $mockCacheItem = $this->createMock(CacheItemInterface::class);
        $mockCacheItem->expects($this->once())
            ->method('isHit')
            ->willReturn(false);

        $this->mockCache->expects($this->once())
            ->method('getItem')
            ->willReturn($mockCacheItem);

        $this->mockCache->expects($this->once())
            ->method('setItem')
            ->with($mockCacheItem, [], CacheInterface::NORMAL);

        $items = $electronService->fetchSupportingContentItemsForProgramme($programme);
        $this->assertEquals([], $items);
    }

    private function makeElectronService(Client $client): ElectronService
    {
        $this->mockCache = $this->createMock(CacheInterface::class);
        $this->xmlParser = new XmlParser();
        $this->contentMapper = new SupportingContentMapper();
        $this->mockLogger = $this->createMock(LoggerInterface::class);
        // Okay. So this is more of an integration test than a unit test at this point.
        // We're testing both ElectronService and HttpApiClient. This does not seem unreasonable to me however.
        $this->httpApiClientFactory = new HttpApiClientFactory($client, $this->mockCache, $this->mockLogger);

        return new ElectronService(
            $this->httpApiClientFactory,
            $this->xmlParser,
            $this->contentMapper,
            'https://api.example.com'
        );
    }
}
