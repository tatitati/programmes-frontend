<?php

namespace App\RecEng;

use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;

class RecEngServiceStub extends RecEngService
{
    /** For use in controller unit tests */
    public function getRecommendations(
        Programme $programme,
        ?Episode $latestEpisode,
        ?Episode $upcomingEpisode,
        ?Episode $lastOnEpisode,
        int $limit = 2
    ): array {
        return [];
    }
}
