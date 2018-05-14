<?php
declare(strict_types = 1);

namespace App\Controller\Traits;

use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;

trait IndexerTrait
{
    protected function getUpcomingBroadcastsIndexedByProgrammePid(
        ProgrammeContainer $programme,
        CollapsedBroadcastsService $collapsedBroadcastsService
    ): array {
        $broadcasts = $collapsedBroadcastsService->findUpcomingByProgrammeWithFullServicesOfNetworksList($programme);
        $upcoming = [];

        foreach ($broadcasts as $broadcast) {
            $programmePid = (string) $broadcast->getProgrammeItem()->getPid();
            // We should choose the first broadcast in the list, as it's the earliest
            if (!isset($upcoming[$programmePid])) {
                $upcoming[$programmePid] = $broadcast;
            }
        }

        return $upcoming;
    }
}
