<?php
declare(strict_types=1);

namespace App\ExternalApi\Recipes\Service;

use App\ExternalApi\Recipes\Domain\RecipesApiResult;
use App\ExternalApi\Recipes\Mapper\RecipeMapper;
use BBC\ProgrammesPagesService\Cache\CacheInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use stdClass;

class RecipesService
{
    /** @var ClientInterface */
    private $client;

    /** @var CacheInterface */
    private $cache;

    /** @var RecipeMapper */
    private $mapper;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $baseUrl;

    public function __construct(
        ClientInterface $client,
        CacheInterface $cache,
        RecipeMapper $mapper,
        LoggerInterface $logger,
        string $baseUrl
    ) {
        $this->client = $client;
        $this->cache = $cache;
        $this->mapper = $mapper;
        $this->logger = $logger;
        $this->baseUrl = $baseUrl;
    }

    public function fetchRecipesByProgramme(Programme $programme, int $limit = 4, int $page = 1): RecipesApiResult
    {
        $cacheKey = $this->cache->keyHelper(__CLASS__, __FUNCTION__, (string) $programme->getPid(), $limit, $page);
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $url = $this->baseUrl . '/by/programme/' . (string) $programme->getPid();
        $url .= '?page=' . $page . '&pageSize=' . $limit . '&sortBy=lastModified&sortSense=desc';

        try {
            $response = $this->client->request('GET', $url);
        } catch (GuzzleException $e) {
            $this->logger->warning('Invalid response from Recipes API. Entity: ' . (string) $programme->getPid());

            $emptyResult = new RecipesApiResult([], 0);

            if ($e instanceof ClientException && $e->getResponse() && $e->getResponse()->getStatusCode() === 404) {
                // 404s get cached for a shorter time
                $this->cache->setItem($cacheItem, $emptyResult, CacheInterface::NORMAL);
            }

            return $emptyResult;
        }

        $response = json_decode($response->getBody()->getContents());
        $result = $this->mapItems($response, (string) $programme->getPid());
        $this->cache->setItem($cacheItem, $result, CacheInterface::MEDIUM);

        return $result;
    }

    public function mapItems(stdClass $items, string $pid): RecipesApiResult
    {
        $recipes = [];
        $total = 0;
        $byProgramme = $items->byProgramme ?? null;

        if ($byProgramme && isset($byProgramme->{$pid})) {
            $total = $byProgramme->{$pid}->count ?? 0;

            foreach (($byProgramme->{$pid}->recipes ?? []) as $recipe) {
                $recipes[] = $this->mapper->mapItem($recipe);
            }
        }

        return new RecipesApiResult($recipes, $total);
    }
}
