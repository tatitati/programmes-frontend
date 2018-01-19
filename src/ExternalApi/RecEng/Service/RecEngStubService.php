<?php

namespace App\ExternalApi\RecEng\Service;

use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class RecEngStubService extends RecEngService
{
    /** For use in controller unit tests */
    public function getRecommendations(
        Programme $programme,
        ?Episode $latestEpisode,
        ?Episode $upcomingEpisode,
        ?Episode $lastOnEpisode,
        int $limit = 2
    ): PromiseInterface {
        return new FulfilledPromise([]);
    }
}
