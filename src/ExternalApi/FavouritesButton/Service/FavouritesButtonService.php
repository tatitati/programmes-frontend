<?php
declare(strict_types = 1);

namespace App\ExternalApi\FavouritesButton\Service;

use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\Exception\ParseException;
use App\ExternalApi\FavouritesButton\Domain\FavouritesButton;
use App\ExternalApi\FavouritesButton\Mapper\FavouritesButtonMapper;
use Closure;
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

    public function getContent(): ?FavouritesButton
    {
        $cacheKey = $this->clientFactory->keyHelper(__CLASS__, __FUNCTION__);

        $client = $this->clientFactory->getHttpApiClient(
            $cacheKey,
            $this->url,
            Closure::fromCallable([$this, 'parseResponse']),
            [],
            null
        );

        return $client->makeCachedRequest();
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
