<?php
declare(strict_types = 1);

namespace App\ExternalApi\Electron\Service;

use BBC\ProgrammesPagesService\Domain\Entity\Programme;

/**
 * Class ElectronStubService
 *
 * Stub class for unit tests
 */
class ElectronStubService extends ElectronService
{
    public function fetchSupportingContentItemsForProgramme(Programme $programme): array
    {
        return [];
    }
}
