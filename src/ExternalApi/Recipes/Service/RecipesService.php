<?php
declare(strict_types=1);

namespace App\ExternalApi\Recipes\Service;

use App\ExternalApi\Client\HttpApiClient;
use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\Exception\ParseException;
use App\ExternalApi\HttpApiService;
use App\ExternalApi\Recipes\Domain\RecipesApiResult;
use App\ExternalApi\Recipes\Mapper\RecipeMapper;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use Closure;

class RecipesService
{
    /** @var RecipeMapper */
    private $mapper;

    /** @var string */
    private $baseUrl;

    /** @var HttpApiClientFactory */
    private $clientFactory;

    public function __construct(
        HttpApiClientFactory $clientFactory,
        RecipeMapper $mapper,
        string $baseUrl
    ) {
        $this->mapper = $mapper;
        $this->baseUrl = $baseUrl;
        $this->clientFactory = $clientFactory;
    }

    /**
     * @param string $pid
     * @param int $limit
     * @param int $page
     * @return PromiseInterface (returns RecipesApiResult when unwrapped)
     */
    public function fetchRecipesByPid(string $pid, int $limit = 4, int $page = 1): PromiseInterface
    {
        $client = $this->makeHttpApiClient($pid, $limit, $page);
        return $client->makeCachedPromise();
    }

    private function makeHttpApiClient(string $pid, int $limit, int $page): HttpApiClient
    {
        $cacheKey = $this->clientFactory->keyHelper(__CLASS__, __FUNCTION__, $pid, $limit, $page);

        $url = $this->baseUrl . '/by/programme/' . urlencode($pid);
        $url .= '?page=' . $page . '&pageSize=' . $limit . '&sortBy=lastModified&sortSense=desc';

        $emptyResult = new RecipesApiResult([], 0);

        return $this->clientFactory->getHttpApiClient(
            $cacheKey,
            $url,
            Closure::fromCallable([$this, 'parseResponse']),
            [$pid],
            $emptyResult
        );
    }

    private function parseResponse(Response $response, string $pid): RecipesApiResult
    {
        $items = json_decode($response->getBody()->getContents(), true);
        if (!$items || !isset($items['byProgramme'][$pid])) {
            throw new ParseException("Invalid Recipes API JSON");
        }
        return $this->mapItems($items['byProgramme'][$pid]);
    }

    private function mapItems(array $items): RecipesApiResult
    {
        $recipes = [];

        $total = $items['count'] ?? 0;

        foreach (($items['recipes'] ?? []) as $recipe) {
            $recipes[] = $this->mapper->mapItem($recipe);
        }

        return new RecipesApiResult($recipes, $total);
    }
}
