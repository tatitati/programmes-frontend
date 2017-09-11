<?php
declare(strict_types = 1);
namespace App\ValueObject;

use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use InvalidArgumentException;

/**
 * Similar to the point of a BroadcastDay
 * @see BroadcastDay
 */
class BroadcastWeek extends BroadcastPeriod
{
    public function __construct(string $date)
    {
        $dateParts = explode('/', $date);
        $year = (int) $dateParts[0];
        $week = (int) str_replace('w', '', $dateParts[1]);
        $blankChronos = ApplicationTime::getLocalTime()->startOfDay(); // Today at midnight
        $this->start = $blankChronos->setISODate($year, $week, 1); // Midnight on Monday
        if ($this->start->year > $year) {
            throw new InvalidArgumentException($year . ' does not have enough weeks in it.');
        }
        $this->end = $this->start->endOfWeek();
    }
}
