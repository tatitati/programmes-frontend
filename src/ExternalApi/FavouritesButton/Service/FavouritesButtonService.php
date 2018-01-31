<?php
declare(strict_types = 1);

namespace App\ExternalApi\FavouritesButton\Service;

use App\ExternalApi\Client\HttpApiClient;
use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\Exception\ParseException;
use App\ExternalApi\FavouritesButton\Domain\FavouritesButton;
use App\ExternalApi\FavouritesButton\Mapper\FavouritesButtonMapper;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;

class FavouritesButtonService
{
    /** @var HttpApiClientFactory */
    private $clientFactory;

    /** @var FavouritesButtonMapper */
    private $favouritesButtonMapper;

    /** @var string */
    private $url;

    public function __construct(HttpApiClientFactory $clientFactory, FavouritesButtonMapper $favouritesButtonMapper, string $url)
    {
        $this->clientFactory = $clientFactory;
        $this->favouritesButtonMapper = $favouritesButtonMapper;
        $this->url = $url;
    }

    /**
     * @return PromiseInterface (Promise returns ?FavouritesButton when unwrapped)
     */
    public function getContent(): PromiseInterface
    {
        $client = $this->makeHttpApiClient();
        return $client->makeCachedPromise();
    }

    private function makeHttpApiClient(): HttpApiClient
    {
        $cacheKey = $this->clientFactory->keyHelper(__CLASS__, __FUNCTION__);
        // Making a call with a cert to some envs results in failures. Hence this
        $guzzleOptions = [
            'cert' => null,
            'ssl_key' => null,
        ];
        if (strpos($this->url, 'www.int') !== false) {
            // Ugly hack because cert is required on int
            $guzzleOptions = [];
        }
        $client = $this->clientFactory->getHttpApiClient(
            $cacheKey,
            $this->url,
            Closure::fromCallable([$this, 'parseResponse']),
            [],
            null,
            CacheInterface::MEDIUM,
            CacheInterface::NORMAL,
            $guzzleOptions
        );

        return $client;
    }

    private function parseResponse(Response $response): FavouritesButton
    {
        $data = json_decode($response->getBody()->getContents(), true);
        if (!isset($data['head'], $data['script'], $data['bodyLast'])) {
            throw new ParseException('Response must contain head, script and bodyLast elements');
        }

        return $this->favouritesButtonMapper->mapItem($data);
    }
}
