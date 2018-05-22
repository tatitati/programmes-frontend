<?php
declare(strict_types=1);

namespace App\ExternalApi\RmsPodcast\Mapper;

use App\ExternalApi\Exception\ParseException;
use App\ExternalApi\RmsPodcast\Domain\RmsPodcast;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;

class RmsPodcastMapper
{
    public function mapItem(string $getContents): RmsPodcast
    {
        $response = json_decode($getContents, true);

        if (!$response || !isset($response['results'])) {
            throw new ParseException("Invalid Recipes API JSON");
        }

        if (!$this->isValidResult($response['results'])) {
            throw new ParseException("Invalid Recipes API JSON");
        }

        $results = $response['results'];


        return new RmsPodcast(new Pid($results[0]['pid']), $results[0]['territory']);
    }

    private function isValidResult($result): bool
    {
        return (isset($result[0]) && isset($result[0]['pid']) && isset($result[0]['territory']));
    }
}
