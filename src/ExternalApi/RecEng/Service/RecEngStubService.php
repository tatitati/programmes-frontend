<?php

namespace App\ExternalApi\RecEng\Service;

use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class RecEngStubService extends RecEngService
{
    /** For use in controller unit tests */
    public function getRecommendations(
        Episode $episode,
        int $limit = 2
    ): PromiseInterface {
        return new FulfilledPromise([]);
    }
}
