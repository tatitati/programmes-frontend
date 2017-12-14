<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Recipes\Service;

use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\Recipes\Domain\Recipe;
use App\ExternalApi\Recipes\Domain\RecipesApiResult;
use App\ExternalApi\Recipes\Mapper\RecipeMapper;
use App\ExternalApi\Recipes\Service\RecipesService;
use BBC\ProgrammesPagesService\Cache\CacheInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerInterface;
use Tests\App\ExternalApi\BaseServiceTestCase;

class RecipesServiceTest extends BaseServiceTestCase
{
    private $mockCache;

    private $mapper;

    private $mockLogger;

    private $httpApiClientFactory;

    public function testFetchRecipesByProgrammeEndToEnd()
    {
        $json = file_get_contents(dirname(__DIR__) . '/JSON/bakeoff.json');
        $response = new Response(200, [], $json);
        $client = $this->client([$response]);
        $recipesService = $this->makeRecipesService($client);
        $items = $recipesService->fetchRecipesByPid('b013pqnm');
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

        $result = $recipesService->fetchRecipesByPid('b006m86d');
        $this->assertEquals($expectedContent, $result);
    }

    public function testExceptionsAreHandled()
    {
        $response = new Response(500, [], 'An Error');
        $client = $this->client([$response]);
        $recipesService = $this->makeRecipesService($client);
        $result = $recipesService->fetchRecipesByPid('b006m86d');
        $this->assertEquals(0, $result->getTotal());
        $this->assertEquals([], $result->getRecipes());
    }

    public function test404sAreCached()
    {
        $response = new Response(404, [], 'An Error');
        $client = $this->client([$response]);
        $recipesService = $this->makeRecipesService($client);
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

        $result = $recipesService->fetchRecipesByPid('b006m86d');
        $this->assertEquals([], $result->getRecipes());
        $this->assertEquals(0, $result->getTotal());
    }

    private function makeRecipesService(Client $client): RecipesService
    {
        $this->mockCache = $this->createMock(CacheInterface::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);
        $this->mapper = new RecipeMapper();
        $this->httpApiClientFactory = new HttpApiClientFactory($client, $this->mockCache, $this->mockLogger);
        return new RecipesService(
            $this->httpApiClientFactory,
            $this->mapper,
            'https://api.example.com'
        );
    }
}
