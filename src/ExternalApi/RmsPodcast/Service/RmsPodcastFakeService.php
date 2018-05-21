<?php
declare(strict_types=1);

namespace App\ExternalApi\RmsPodcast\Service;

use App\ExternalApi\RmsPodcast\Domain\RmsPodcast;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class RmsPodcastFakeService extends RmsPodcastService
{
    public function getPodcast(Pid $pid): PromiseInterface
    {
        $podcastsInServer = ['b006q2x0'];

        $isFoundPodcastInServer = in_array((string) $pid, $podcastsInServer);

        if ($isFoundPodcastInServer) {
            return new FulfilledPromise(
                new RmsPodcast($pid)
            );
        }

        return new FulfilledPromise(null);
    }
}
