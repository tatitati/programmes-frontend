<?php
declare(strict_types=1);

namespace App\ExternalApi\RecEng\Service;

use App\ExternalApi\Exception\ParseException;
use BBC\ProgrammesPagesService\Cache\CacheInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

/**
 * Recommendation Engine
 *
 * This class interfaces with the Recommendation Engine API to fetch recommendations based on a given episode
 * This is primarily used within the page footer
 */
class RecEngService
{
    /** @var ClientInterface */
    private $client;

    /** @var CacheInterface */
    private $cache;

    /** @var string */
    private $audioKey;

    /** @var string */
    private $videoKey;

    /** @var ProgrammesService */
    private $programmesService;

    /** @var string */
    private $baseUrl;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ClientInterface $client,
        CacheInterface $cache,
        string $audioKey,
        string $videoKey,
        ProgrammesService $programmesService,
        LoggerInterface $logger,
        string $baseUrl
    ) {
        $this->client = $client;
        $this->cache = $cache;
        $this->audioKey = $audioKey;
        $this->videoKey = $videoKey;
        $this->programmesService = $programmesService;
        $this->baseUrl = $baseUrl;
        $this->logger = $logger;
    }

    /**
     * Returns an array of Programme objects which are fetched based on RecEng results
     * Will return an empty array if not results were found or RecEng cannot be reached
     *
     * @param Programme $programme
     * @param Episode|null $latestEpisode
     * @param Episode|null $upcomingEpisode
     * @param Episode|null $lastOnEpisode
     * @param int $limit
     * @return Programme[]
     */
    public function getRecommendations(
        Programme $programme,
        ?Episode $latestEpisode,
        ?Episode $upcomingEpisode,
        ?Episode $lastOnEpisode,
        int $limit = 2
    ): array {
        $programmeEpisode = $this->getProgrammeEpisode($programme, $latestEpisode, $upcomingEpisode, $lastOnEpisode);

        if (!$programmeEpisode) {
            return [];
        }

        $programmePid = $programmeEpisode->getPid();

        $key = $this->cache->keyHelper(__CLASS__, __FUNCTION__, (string) $programmePid);
        $cacheItem = $this->cache->getItem($key);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $recEngKey = $programmeEpisode->isVideo() ? $this->videoKey : $this->audioKey;

        $requestUrl = $this->baseUrl . '?key=' . $recEngKey . '&id=' . (string) $programmePid;

        try {
            $response = $this->client->request('GET', $requestUrl);
        } catch (GuzzleException $e) {
            if ($e instanceof ClientException && $e->getResponse() && $e->getResponse()->getStatusCode() === 404) {
                // 404s get cached for a shorter time
                $this->cache->setItem($cacheItem, [], CacheInterface::NORMAL);
            }
            return [];
        }
        $recEngRespose = $response->getBody()->getContents();

        try {
            $pids = $this->parseResult($recEngRespose, $limit);
        } catch (ParseException $e) {
            $this->logger->error($e->getMessage() . "Url was: " . $requestUrl);
            return [];
        }

        $result = ($pids === []) ? [] : $this->programmesService->findByPids($pids);
        $this->cache->setItem($cacheItem, $result, CacheInterface::MEDIUM);
        return $result;
    }

    /**
     * recEng requires an Episode pid, so this determines which to use based on the type of Programme passed into it
     * Takes nullable args of latest, upcoming and last on episodes as this is called in TLEC controller and these are already fetched
     */
    private function getProgrammeEpisode(
        Programme $programme,
        ?Episode $latestEpisode,
        ?Episode $upcomingEpisode,
        ?Episode $lastOnEpisode
    ): ?Episode {
        if ($programme instanceof Episode) {
            return $programme;
        }

        if ($programme instanceof Clip) {
            if ($programme->getParent() && $programme->getParent() instanceof Episode) {
                return $programme->getParent();
            }
        }

        if ($programme instanceof ProgrammeContainer) {
            if ($latestEpisode) {
                return $latestEpisode;
            }

            if ($upcomingEpisode) {
                return $upcomingEpisode;
            }

            if ($lastOnEpisode) {
                return $lastOnEpisode;
            }
        }

        return null;
    }

    /**
     * Returns an array of at most limit Pid objects from the given response
     *
     * @param string $response
     * @param int $limit
     * @return Pid[]
     */
    private function parseResult(string $response, int $limit): array
    {
        $results = json_decode($response);

        if (!isset($results->recommendations)) {
            throw new ParseException("Invalid data from Recommendations API");
        }

        $recommendations = $results->recommendations;
        $pids = [];
        $numResults = 0;

        if ($limit <= 0) {
            return [];
        }

        foreach ($recommendations as $recItem) {
            $recommendation = str_replace('urn:bbc:pips:', '', $recItem->ref);

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
