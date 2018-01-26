<?php
declare(strict_types=1);

namespace App\ExternalApi\RecEng\Service;

use App\ExternalApi\Client\HttpApiClient;
use App\ExternalApi\Client\HttpApiClientFactory;
use Closure;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use App\ExternalApi\Exception\ParseException;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;

/**
 * Recommendation Engine
 *
 * This class interfaces with the Recommendation Engine API to fetch recommendations based on a given episode
 * This is primarily used within the page footer
 */
class RecEngService
{
    /** @var HttpApiClientFactory */
    private $clientFactory;

    /** @var string */
    private $audioKey;

    /** @var string */
    private $videoKey;

    /** @var ProgrammesService */
    private $programmesService;

    /** @var string */
    private $baseUrl;

    public function __construct(
        HttpApiClientFactory $clientFactory,
        string $audioKey,
        string $videoKey,
        ProgrammesService $programmesService,
        string $baseUrl
    ) {
        $this->clientFactory = $clientFactory;
        $this->audioKey = $audioKey;
        $this->videoKey = $videoKey;
        $this->programmesService = $programmesService;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Returns a promise of an array of Programme objects which are fetched based on RecEng results
     * Will return an empty array if not results were found or RecEng cannot be reached
     *
     * @param Episode $episode
     * @param int $limit
     * @return PromiseInterface (returns Programme[] when unwrapped)
     */
    public function getRecommendations(
        Episode $episode,
        int $limit = 2
    ): PromiseInterface {
        $client = $this->makeClient($episode, $limit);
        return $client->makeCachedPromise();
    }

    private function makeClient(Episode $programmeEpisode, int $limit): HttpApiClient
    {
        $programmePid = $programmeEpisode->getPid();
        $cacheKey = $this->clientFactory->keyHelper(__CLASS__, __FUNCTION__, (string) $programmePid);
        $recEngKey = $programmeEpisode->isVideo() ? $this->videoKey : $this->audioKey;
        $requestUrl = $this->baseUrl . '?key=' . $recEngKey . '&id=' . (string) $programmePid;

        return $this->clientFactory->getHttpApiClient(
            $cacheKey,
            $requestUrl,
            Closure::fromCallable([$this, 'parseResponse']),
            [$limit]
        );
    }

    /**
     * Returns an array of at most limit Pid objects from the given response
     *
     * @param Response $response
     * @param int $limit
     * @return Programme[]
     */
    private function parseResponse(Response $response, int $limit): array
    {
        $responseBody = $response->getBody()->getContents();
        $results = json_decode($responseBody, true);

        if (!isset($results['recommendations'])) {
            throw new ParseException("Invalid data from Recommendations API");
        }

        $pids = $this->getPidsFromRecommendations($results['recommendations'], $limit);

        $result = [];
        if ($pids) {
            $result = $this->programmesService->findByPids($pids);
        }
        return $result;
    }

    private function getPidsFromRecommendations(array $recommendations, int $limit)
    {
        $pids = [];
        $numResults = 0;

        if ($limit <= 0) {
            return [];
        }

        foreach ($recommendations as $recItem) {
            $recommendation = str_replace('urn:bbc:pips:', '', $recItem['ref']);

            try {
                $pid = new Pid($recommendation);
                $pids[] = $pid;
                $numResults++;
            } catch (InvalidArgumentException $e) {
            }

            if ($numResults >= $limit) {
                return $pids;
            }
        }
        return $pids;
    }
}
