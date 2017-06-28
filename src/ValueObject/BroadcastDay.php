<?php
declare(strict_types = 1);
namespace App\ValueObject;

use BBC\ProgrammesPagesService\Domain\Enumeration\NetworkMediumEnum;
use Cake\Chronos\Chronos;
use InvalidArgumentException;

/**
 * Encapsulates the concept of a Broadcast Day, as TV and Radio have differing
 * opinions on what that is.
 *
 * Given a point in time, return the Broadcast Day range that point is within.
 *
 * For TV, a Broadcast Day is 24 hours long, starting at 6am and running till
 * 6am the next day. As the day starts at 6 am, The Broadcast Day for
 * 2017-02-05T03:00:00 is 2017-02-04T06:00:00 till 2017-02-05T06:00:00.
 *
 * For Radio, a Broadcast Day is 30 hours long, starting at midnight and running
 * till 6am the next day. A single point in time in the early morning will fit
 * into two "days" we resolve this by ignoring the first 6 hours. The day for
 * 2017-02-05T03:00:00 is 2017-02-05T00:00:00 till 2017-02-06T06:00:00;
 * NOT 2017-02-04T00:00:00 till 2017-02-05T06:00:00.
 */
class BroadcastDay
{
    /** @var Chronos */
    private $start;

    /** @var Chronos */
    private $end;

    public function __construct(Chronos $dateTime, string $networkMedium)
    {
        if (!in_array($networkMedium, [NetworkMediumEnum::RADIO, NetworkMediumEnum::TV, NetworkMediumEnum::UNKNOWN])) {
            throw new InvalidArgumentException(sprintf(
                'Called new BroadcastDay() with an invalid networkMedium. Expected one of %s but got "%s"',
                '"' . implode('", "', [NetworkMediumEnum::RADIO, NetworkMediumEnum::TV, NetworkMediumEnum::UNKNOWN]) . '"',
                $networkMedium
            ));
        }

        if ($networkMedium == NetworkMediumEnum::TV) {
            // If the time is before 6am, then the broadcast day begins at 6am
            // on the previous day
            if ($dateTime->hour < 6) {
                $dateTime = $dateTime->subDays(1);
            }

            $this->start = $dateTime->setTime(6, 0, 0);
            $this->end = $this->start->addHours(24);
        } else {
            $this->start = $dateTime->setTime(0, 0, 0);
            $this->end = $this->start->addHours(30);
        }
    }

    public function isNow(): bool
    {
        return Chronos::now()->between($this->start, $this->end);
    }

    public function start(): Chronos
    {
        return $this->start;
    }

    public function end(): Chronos
    {
        return $this->end;
    }
}
