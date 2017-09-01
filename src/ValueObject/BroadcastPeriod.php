<?php
declare(strict_types = 1);
namespace App\ValueObject;

use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\Enumeration\NetworkMediumEnum;
use Cake\Chronos\Chronos;
use InvalidArgumentException;

abstract class BroadcastPeriod
{
    /** @var Chronos */
    protected $start;

    /** @var Chronos */
    protected $end;

    public function serviceIsActiveInThisPeriod(Service $service): bool
    {
        return (!$service->getStartDate() || $service->getStartDate()->lte($this->end)) && (!$service->getEndDate() || $this->start->lt($service->getEndDate()));
    }

    public function start(): Chronos
    {
        return $this->start;
    }

    public function end(): Chronos
    {
        return $this->end;
    }

    public function isNow(): bool
    {
        return ApplicationTime::getTime()->between($this->start, $this->end);
    }

    protected function validateNetworkMedium(string $networkMedium)
    {
        if (!in_array($networkMedium, [NetworkMediumEnum::RADIO, NetworkMediumEnum::TV, NetworkMediumEnum::UNKNOWN])) {
            throw new InvalidArgumentException(sprintf(
                'Called new BroadcastPeriod() with an invalid networkMedium. Expected one of %s but got "%s"',
                '"' . implode('", "', [NetworkMediumEnum::RADIO, NetworkMediumEnum::TV, NetworkMediumEnum::UNKNOWN]) . '"',
                $networkMedium
            ));
        }
    }
}
