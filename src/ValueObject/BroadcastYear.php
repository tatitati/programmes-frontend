<?php
declare(strict_types = 1);

namespace App\ValueObject;

use BBC\ProgrammesPagesService\Domain\Enumeration\NetworkMediumEnum;
use Cake\Chronos\Chronos;
use Cake\Chronos\ChronosInterface;

/**
 * Similar to the point of a BroadcastDay
 * @see BroadcastDay
 */
class BroadcastYear extends BroadcastPeriod
{
    public function __construct(ChronosInterface $date, string $networkMedium)
    {
        $this->validateNetworkMedium($networkMedium);

        if ($networkMedium == NetworkMediumEnum::TV) {
            $this->start = Chronos::create($date->year, 1, 1, 6, 0, 0);
        } else {
            $this->start = Chronos::create($date->year, 1, 1, 0, 0, 0);
        }
        $this->end = $this->start->endOfYear();
    }
}
