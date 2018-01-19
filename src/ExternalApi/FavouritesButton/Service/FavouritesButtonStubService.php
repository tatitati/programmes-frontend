<?php
declare(strict_types = 1);

namespace App\ExternalApi\FavouritesButton\Service;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class FavouritesButtonStubService extends FavouritesButtonService
{
    public function getContent(): PromiseInterface
    {
        return new FulfilledPromise(null);
    }
}
