<?php
declare(strict_types=1);

namespace App\ExternalApi\Tupac\Service;

use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\Exception\MultiParseException;
use App\ExternalApi\Tupac\Domain\Record;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use Closure;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;

class TupacService
{
    /** @var string */
    private $baseUrl;

    /** @var HttpApiClientFactory */
    private $clientFactory;

    public function __construct(
        HttpApiClientFactory $clientFactory,
        string $baseUrl
    ) {
        $this->clientFactory = $clientFactory;
        $this->baseUrl = $baseUrl;
    }

    public function fetchRecordsByIds(array $recordIds, bool $isUk = false): PromiseInterface
    {
        if (empty($recordIds)) {
            return new FulfilledPromise([]);
        }
        // TUPAC can return up to 100 results per request so it's required to split the API request into chunks of 100 records
        $chunks = array_chunk($recordIds, 100);
        $urls = [];
        foreach ($chunks as $recordsIdsArrayChunk) {
            $urls[] = $this->generateRequestUrl($recordsIdsArrayChunk, $isUk);
        }

        $recordIdsHash = md5(implode('_', $recordIds));
        $cacheKey = $this->clientFactory->keyHelper(__CLASS__, __FUNCTION__, $recordIdsHash);
        $client = $this->clientFactory->getHttpApiMultiClient(
            $cacheKey,
            $urls,
            Closure::fromCallable([$this, 'parseResponse']),
            [],
            [],
            CacheInterface::MEDIUM
        );

        return $client->makeCachedPromise();
    }

    private function generateRequestUrl(array $recordIds, bool $isUk = false): string
    {
        $recordIdsAsQueryParameters = '';
        foreach ($recordIds as $recordId) {
            $recordIdsAsQueryParameters .= '&id=' . urlencode($recordId);
        }
        $ukQueryParameter = $isUk ? '1' : '0';
        return $this->baseUrl . '/music/v2/records?context=programmes&resultsPerPage=100&uk=' . $ukQueryParameter . $recordIdsAsQueryParameters;
    }

    private function parseResponse(array $responses): array
    {
        $results = [];
        foreach ($responses as $key => $response) {
            if (!$response instanceof Response) {
                throw new MultiParseException($key, "TUPAC callback received non-response object!");
            }
            $apiResponse = json_decode($response->getBody()->getContents(), true);
            if (!$apiResponse || !isset($apiResponse['data'])) {
                throw new MultiParseException($key, "TUPAC API response is empty or invalid json");
            }
            $results = array_merge($results, $this->mapItems($apiResponse));
        }

        return $results;
    }

    private function mapItems(array $items): array
    {
        $records = [];

        foreach ($items['data'] as $record) {
            $records[] = new Record(
                $record['id'],
                $record['title'] ?? '',
                $record['artistName'] ?? '',
                $record['artistGid'] ?? '',
                $record['recordImagePid'] ?? '',
                $record['preferredRecordAudio']['duration'] ?? null,
                $record['preferredRecordAudio']['identifier'] ?? '',
                !empty($record['preferredRecordAudio']['resourceType']) ? strtolower($record['preferredRecordAudio']['resourceType']) : 'mp3',
                !empty($record['preferredRecordAudio']['audioType']) ? strtolower($record['preferredRecordAudio']['audioType']) : 'snippet'
            );
        }

        return $records;
    }
}
