<?php
declare(strict_types = 1);
namespace App\Controller\Traits;

trait UtcOffsetValidatorTrait
{
    public function isValidUtcOffset(?string $utcOffset): bool
    {
        if (is_null($utcOffset)) {
            return true;
        }

        // utc offset always needs the symbol +/-, otherwise you get
        // an "Unknown or bad timezone (<hour>:<minutes>)" exception
        // cause of this we validate that the symbol exits
        if (!preg_match('/(?<SYMBOL>\+|-)(?<HOUR>\d{2}):(?<MINUTES>\d{2})/', $utcOffset, $utcMatches)) {
            return false;
        }

        $symbol = $utcMatches['SYMBOL'];
        $absHour = $utcMatches['HOUR'];
        $minutes = $utcMatches['MINUTES'];

        $hour = $symbol === '+' ? $absHour : -$absHour;

        if ($hour < -12 || $hour > 14) {
            return false;
        }

        if (!empty($minutes) && !in_array($minutes, ['00', '15', '30', '45'])) {
            return false;
        }

        return true;
    }
}
