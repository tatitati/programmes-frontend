<?php
declare(strict_types = 1);
namespace App\Controller;

use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;

class SchedulesByDayController extends BaseController
{
    public function __invoke(Pid $pid, ?string $date)
    {
        // TOD if date is absent use today's date
        // TODO see if we can create a valid date from $date else throw 404
        // TODO make sure date is in a sensible range - year is between 1900
        // and 2100 else throw 404 (no point querying the DB for dates we know
        // won't have any results)
        // TODO lookup Service based on pid
        // TODO get Broadcasts for this date (tv day or radio day)

        return $this->renderWithChrome('schedules/by_day.html.twig', [
            'service' => null,
        ]);
    }
}
