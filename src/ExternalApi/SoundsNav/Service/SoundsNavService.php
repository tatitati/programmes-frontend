<?php
declare(strict_types = 1);

namespace App\ExternalApi\SoundsNav\Service;

use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\Client\HttpApiMultiClient;
use App\ExternalApi\Exception\MultiParseException;
use App\ExternalApi\SoundsNav\Domain\SoundsNav;
use App\ExternalApi\SoundsNav\Mapper\SoundsNavMapper;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;

class SoundsNavService
{
    private $clientFactory;
    private $url;
    private $soundsNavMapper;

    public function __construct(
        HttpApiClientFactory $clientFactory,
        SoundsNavMapper $soundsNavMapper,
        string $url
    ) {
        $this->clientFactory = $clientFactory;
        $this->soundsNavMapper = $soundsNavMapper;
        $this->url = $url;
    }

    /**
     * @return PromiseInterface
     */
    public function getContent(): PromiseInterface
    {
        $client = $this->makeHttpApiClient();
        return $client->makeCachedPromise();
    }

    private function makeHttpApiClient(): HttpApiMultiClient
    {
        $cacheKey = $this->clientFactory->keyHelper(__CLASS__, __FUNCTION__);

        return $this->clientFactory->getHttpApiMultiClient(
            $cacheKey,
            [$this->url],
            Closure::fromCallable([$this, 'parseResponse']),
            [],
            null,
            CacheInterface::LONG,
            CacheInterface::NORMAL
        );
    }

    /**
     * @param Response[] $responses
     * @return SoundsNav
     */
    private function parseResponse(array $responses): SoundsNav
    {
        $data = json_decode($responses[0]->getBody()->getContents(), true);
        if (!isset($data['head'], $data['body'], $data['foot'])) {
            throw new MultiParseException(0, 'Response must contain head, body and foot elements');
        }

        return $this->soundsNavMapper->mapItem($data);
    }
}
