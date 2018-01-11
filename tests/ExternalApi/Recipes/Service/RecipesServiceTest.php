<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Recipes\Service;

use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\Recipes\Domain\Recipe;
use App\ExternalApi\Recipes\Domain\RecipesApiResult;
use App\ExternalApi\Recipes\Mapper\RecipeMapper;
use App\ExternalApi\Recipes\Service\RecipesService;
use BBC\ProgrammesPagesService\Cache\CacheInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Cache\CacheItemInterface;
use Tests\App\ExternalApi\BaseServiceTestCase;

class RecipesServiceTest extends BaseServiceTestCase
{
    public function setUp()
    {
        $this->setUpCache();
        $this->setUpLogger();
    }

    public function testFetchRecipesByProgrammeEndToEnd()
    {
        $history = [];

        $json = file_get_contents(dirname(__DIR__) . '/JSON/bakeoff.json');
        $response = new Response(200, [], $json);
        $client = $this->client([$response], $history);
        $recipesService = $this->service($client);
        $items = $recipesService->fetchRecipesByPid('b013pqnm');
        $this->assertCount(4, $items->getRecipes());
        $this->assertEquals('Stollen', $items->getRecipes()[0]->getTitle());

        // Ensure multiple calls use the cache instead of making multiple requests
        $recipesService->fetchRecipesByPid('b013pqnm');
        $this->assertCount(1, $history);
    }

    public function testExceptionsAreHandled()
    {
        $response = new Response(500, [], 'An Error');
        $client = $this->client([$response]);
        $recipesService = $this->service($client);
        $result = $recipesService->fetchRecipesByPid('b006m86d');
        $this->assertEquals(0, $result->getTotal());
        $this->assertEquals([], $result->getRecipes());
    }

    public function test404sAreCached()
    {
        $service = $this->service(
            $this->client([new Response(404, [], '')])
        );

        $result = $service->fetchRecipesByPid('b006m86d');

        $this->assertEquals(new RecipesApiResult([], 0), $result);

        // Assert an empty result was stored in the cache
        $validCacheValues = $this->validCacheValues();
        $this->assertCount(1, $validCacheValues);
        $this->assertEquals(new RecipesApiResult([], 0), reset($validCacheValues));
    }

    private function service($client): RecipesService
    {
        return new RecipesService(
            new HttpApiClientFactory($client, $this->cache, $this->logger),
            new RecipeMapper(),
            'https://api.example.com'
        );
    }
}
