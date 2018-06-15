<?php
declare(strict_types=1);

namespace App\ExternalApi\Tupac\Service;

use App\ExternalApi\Client\HttpApiClient;
use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\Exception\ParseException;
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
        $client = $this->makeHttpApiClient($recordIds, $isUk);
        return $client->makeCachedPromise();
    }

    private function makeHttpApiClient(array $recordIds, bool $isUk = false): HttpApiClient
    {
        $recordIdsHash = md5(implode('_', $recordIds));
        $cacheKey = $this->clientFactory->keyHelper(__CLASS__, __FUNCTION__, $recordIdsHash);

        $recordIdsAsQueryParameters = '';
        foreach ($recordIds as $recordsId) {
            $recordIdsAsQueryParameters .= '&id=' . urlencode($recordsId);
        }

        $ukQueryParameter = $isUk ? '1' : '0';
        $url = $this->baseUrl . '/music/v2/records?context=programmes&resultsPerPage=1000&uk=' . $ukQueryParameter . $recordIdsAsQueryParameters;

        return $this->clientFactory->getHttpApiClient(
            $cacheKey,
            $url,
            Closure::fromCallable([$this, 'parseResponse'])
        );
    }

    private function parseResponse(Response $response): array
    {
        $apiResponse = json_decode($response->getBody()->getContents(), true);
        if (!$apiResponse || !isset($apiResponse['data'])) {
            throw new ParseException("TUPAC API response is empty or invalid json");
        }
        return $this->mapItems($apiResponse);
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
