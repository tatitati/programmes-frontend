<?php
declare(strict_types = 1);

namespace App\ExternalApi\Ada\Service;

use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Class AdaClassStubService
 *
 * Stub class for unit tests
 */
class AdaProgrammeStubService extends AdaProgrammeService
{
    public function findSuggestedByProgrammeItem(Programme $programme, int $limit = 3): PromiseInterface
    {
        return new FulfilledPromise([]);
    }
}
