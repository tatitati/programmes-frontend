<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Electron\Service;

use App\ExternalApi\Electron\Service\ElectronService;
use App\ExternalApi\Electron\Domain\SupportingContentItem;
use App\ExternalApi\Electron\Mapper\SupportingContentMapper;
use App\ExternalApi\Recipes\Domain\Recipe;
use App\ExternalApi\Recipes\Domain\RecipesApiResult;
use App\ExternalApi\Recipes\Mapper\RecipeMapper;
use App\ExternalApi\Recipes\Service\RecipesService;
use App\ExternalApi\XmlParser\XmlParser;
use BBC\ProgrammesPagesService\Cache\CacheInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerInterface;

class RecipesServiceTest extends TestCase
{
    private $mockCache;

    private $mapper;

    private $mockLogger;

    public function testFetchRecipesByProgrammeEndToEnd()
    {
        $json = file_get_contents(dirname(__DIR__) . '/JSON/bakeoff.json');
        $response = new Response(200, [], $json);
        $client = $this->makeGuzzleClientToRespondWith($response);
        $recipesService = $this->makeRecipesService($client);
        $programme = $this->createMock(Brand::class);
        $programme->expects($this->atLeastOnce())->method('getPid')->willReturn(new Pid('b013pqnm'));
        $items = $recipesService->fetchRecipesByProgramme($programme);
        $this->assertCount(4, $items->getRecipes());
        $this->assertEquals('Stollen', $items->getRecipes()[0]->getTitle());
    }

    public function testItemRetrievedFromCache()
    {
        $client = $this->createMock(Client::class);
        $recipesService = $this->makeRecipesService($client);
        $this->mockCache->expects($this->atLeastOnce())
            ->method('keyHelper')
            ->willReturn('mockCacheKey');

        $expectedContent = new RecipesApiResult([new Recipe('id', 'title', 'description')], 1);
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
        $result = $recipesService->fetchRecipesByProgramme($programme);
        $this->assertEquals($expectedContent, $result);
    }

    public function testExceptionsAreHandled()
    {
        $response = new Response(500, [], 'An Error');
        $client = $this->makeGuzzleClientToRespondWith($response);
        $recipesService = $this->makeRecipesService($client);
        $programme = $this->createMock(Brand::class);
        $programme->expects($this->atLeastOnce())->method('getPid')->willReturn(new Pid('b006m86d'));
        $result = $recipesService->fetchRecipesByProgramme($programme);
        $this->assertEquals(0, $result->getTotal());
        $this->assertEquals([], $result->getRecipes());
    }

    public function test404sAreCached()
    {
        $response = new Response(404, [], 'An Error');
        $client = $this->makeGuzzleClientToRespondWith($response);
        $recipesService = $this->makeRecipesService($client);
        $programme = $this->createMock(Brand::class);
        $programme->expects($this->atLeastOnce())->method('getPid')->willReturn(new Pid('b006m86d'));

        $mockCacheItem = $this->createMock(CacheItemInterface::class);
        $mockCacheItem->expects($this->once())
            ->method('isHit')
            ->willReturn(false);

        $this->mockCache->expects($this->once())
            ->method('getItem')
            ->willReturn($mockCacheItem);

        $empty = new RecipesApiResult([], 0);
        $this->mockCache->expects($this->once())
            ->method('setItem')
            ->with($mockCacheItem, $empty, CacheInterface::NORMAL);

        $result = $recipesService->fetchRecipesByProgramme($programme);
        $this->assertEquals([], $result->getRecipes());
        $this->assertEquals(0, $result->getTotal());
    }

    private function makeRecipesService(Client $client): RecipesService
    {
        $this->mockCache = $this->createMock(CacheInterface::class);
        $this->mapper = new RecipeMapper();
        $this->mockLogger = $this->createMock(LoggerInterface::class);
        return new RecipesService(
            $client,
            $this->mockCache,
            $this->mapper,
            $this->mockLogger,
            'https://api.example.com'
        );
    }

    private function makeGuzzleClientToRespondWith(Response $response): Client
    {
        $mockHandler = new MockHandler();
        $container = [];
        $stack = HandlerStack::create($mockHandler);
        $history = Middleware::history($container);
        $stack->push($history);

        $client = new Client(['handler' => $stack]);
        $mockHandler->append($response);
        return $client;
    }
}
