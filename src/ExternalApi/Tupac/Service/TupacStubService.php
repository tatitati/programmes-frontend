<?php
declare(strict_types=1);

namespace App\ExternalApi\Tupac\Service;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class TupacStubService extends TupacService
{
    /** For use in controller unit tests */
    public function fetchRecordsByIds(array $recordsIds, bool $isUk = false): PromiseInterface
    {
        return new FulfilledPromise([]);
    }
}
