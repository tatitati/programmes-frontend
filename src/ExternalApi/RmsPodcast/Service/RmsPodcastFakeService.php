<?php
declare(strict_types=1);

namespace App\ExternalApi\RmsPodcast\Service;

use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class RmsPodcastFakeService extends RmsPodcastService
{
    public function getPodcast(Pid $pid): PromiseInterface
    {
        $podcastsStoredInRmsPodcastsServer = [
            'b006q2x0' => $this->get200JsonResponse(),
        ];

        $isPodcast = in_array((string) $pid, $podcastsStoredInRmsPodcastsServer);

        return new FulfilledPromise(
            $isPodcast ? $this->get200JsonResponse() : $this->get404JsonResponse()
        );
    }

    private function get200JsonResponse()
    {
        return <<< EOF
{
    "\$schema": "https://rms.api.bbc.co.uk/docs/swagger.json#/definitions/PodcastsResponse",
    "total": 1,
    "limit": 100,
    "offset": 0,
    "results": [
    {
        "pid": "b006qykl",
        "type": "podcast",
        "entity_type": "brand",
    }
  ]
}
EOF;
    }

    private function get404JsonResponse()
    {
        return 'Not found this podcast bla bla bla bla';
    }
}
