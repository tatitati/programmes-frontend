<?php
declare(strict_types = 1);

namespace App\ExternalApi\Ada\Service;

use App\ExternalApi\Ada\Domain\AdaClass;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;

/**
 * Class ElectronStubService
 *
 * Stub class for unit tests
 */
class AdaClassStubService extends AdaClassService
{
    /**
     * @return AdaClass[]
     */
    public function findRelatedClassesByContainer(
        Programme $programme,
        bool $countWithinTleo = true
    ): array {
        return [];
    }
}
