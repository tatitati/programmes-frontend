<?php
declare(strict_types = 1);

namespace App\ExternalApi\Electron\Service;

use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Class ElectronStubService
 *
 * Stub class for unit tests
 */
class ElectronStubService extends ElectronService
{
    public function fetchSupportingContentItemsForProgramme(Programme $programme): PromiseInterface
    {
        return new FulfilledPromise([]);
    }
}
