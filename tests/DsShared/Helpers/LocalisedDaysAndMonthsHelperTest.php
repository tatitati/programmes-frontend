<?php
declare(strict_types=1);

namespace Tests\App\DsShared\Helpers;

use App\DsShared\Helpers\LocalisedDaysAndMonthsHelper;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;
use RMP\Translate\TranslateFactory;

class LocalisedDaysAndMonthsHelperTest extends TestCase
{
    /** @var LocalisedDaysAndMonthsHelper */
    private $helper;

    public function setup()
    {
        $this->helper = new LocalisedDaysAndMonthsHelper(new TranslateProvider(new TranslateFactory()));
    }

    public function teardown()
    {
        ApplicationTime::blank();
    }

    /** @dataProvider getFormatedDayProvider */
    public function testGetFormatedDay(Chronos $date, string $expected)
    {
        ApplicationTime::setTime((new Chronos('2017-7-21 12:00:00'))->timestamp);
        $this->assertSame($expected, $this->helper->getFormatedDay($date));
    }

    public function getFormatedDayProvider(): array
    {
        return [
            'Today' => [new Chronos('2017-7-21 12:00:00'), 'Today'],
            'Tomorrow' => [new Chronos('2017-7-22 12:00:00'), 'Tomorrow'],
            'Yesterday' => [new Chronos('2017-7-20 12:00:00'), 'Yesterday'],
            'Christmas Eve' => [new Chronos('2017-12-24 12:00:00'), 'Christmas Eve 2017'],
            'Christmas Day' => [new Chronos('2017-12-25 12:00:00'), 'Christmas Day 2017'],
            'Boxing Day' => [new Chronos('2017-12-26 12:00:00'), 'Boxing Day 2017'],
            'New Years Day' => [new Chronos('2017-01-01 12:00:00'), 'New Year\'s Day 2017'],
            'Next weekday' => [new Chronos('2017-7-28 12:00:00'), 'Next Friday'],
            'Last weekday' => [new Chronos('2017-7-14 12:00:00'), 'Last Friday'],
            'Weekday' => [new Chronos('2017-7-24 12:00:00'), 'Monday'],
            'Date' => [new Chronos('2017-6-14 12:00:00'), 'Wed 14 Jun 2017'],
            'Day with one digit' => [new Chronos('2017-6-06 12:00:00'), 'Tue 6 Jun 2017'],
        ];
    }

    /**
     * @dataProvider programmeStartingDatesProvider
     */
    public function testListDatesCharacterization(string $programmeStartAt, int $diffInDaysFromNow, string $expectedTranslation)
    {
        ApplicationTime::setTime((new Chronos('2018-05-21 14:30:00'))->timestamp);
        $now = ApplicationTime::getLocalTime();

        $dateProgram = new Chronos($programmeStartAt);
        $this->assertSame($diffInDaysFromNow, $now->diffInDays($dateProgram));
        $this->assertSame($expectedTranslation, $this->helper->getFormatedDay($dateProgram));
    }

    public function programmeStartingDatesProvider()
    {
            // rest
                // ..
                $lastLastSaturday = '2018-05-12 ';
            // within last 8 days
                $lastLasttSunday = '2018-05-13 ';
                $lastMonday = '2018-05-14 ';
                $lastTuesday = '2018-05-15 ';
            // within last 5 days
                $lastWednesday = '2018-05-16 ';
                $lastThursday = '2018-05-17 ';
                $lastFriday = '2018-05-18 ';
                $lastSaturday = '2018-05-19 ';
                $lastSunday = '2018-05-20 ';
        // TODAY - week 1
        $todayMonday = '2018-05-21 ';
            // within 5 days
                $nextTuesday = '2018-05-22 ';
                $nextWednesday = '2018-05-23 ';
                $nextThursday = '2018-05-24 ';
                $nextFriday = '2018-05-25 ';
                $nextSaturday = '2018-05-26 ';
            // within 8 days
                $nextSunday = '2018-05-27 ';
                $nextNextMonday = '2018-05-28 ';
                $nextNextTuesday = '2018-05-29 ';
            // rest
                $nextNextWednesday = '2018-05-30 ';
                // ...

        return [
            // rest
                '-11|  Date - Now = 9 days' => [$lastLastSaturday . '14:29:00', 9, 'Sat 12 May 2018'],
                '-10|  Date - Now = 8 days' => [$lastLastSaturday . '14:31:00', 8, 'Sat 12 May 2018'],
            // in past 8 days
                '-9|  Date - Now = 8 days' => [$lastLasttSunday . '14:29:00', 8, 'Last Sunday'],
                '-8|  Date - Now = 7 days' => [$lastLasttSunday . '14:31:00', 7, 'Last Sunday'],

                '-7|  Date - Now = 6 days' => [$lastTuesday . '14:29:00', 6, 'Last Tuesday'],
                '-6|  Date - Now = 5 days' => [$lastTuesday . '14:31:00', 5, 'Last Tuesday'],
            // in past 5 days
                '-5|  Date - Now = 5 days' => [$lastWednesday . '14:29:00', 5, 'Wednesday'],
                '-4|  Date - Now = 4 days' => [$lastWednesday . '14:31:00', 4, 'Wednesday'],

                '-3|  Date - Now = 5 days' => [$lastFriday . '14:29:00', 3, 'Friday'],
                '-2|  Date - Now = 4 days' => [$lastFriday . '14:31:00', 2, 'Friday'],
            // yesterday
                '-1|  Date - Now = 1 days' => [$lastSunday . '14:29:00', 1, 'Yesterday'],
            // today
                '0| ' => [$todayMonday, 0, 'Today'],
            // tomorrow = true
                '1|  Date - Now = 0 days' => [$nextTuesday . '14:29:00', 0, 'Tomorrow'],
                '2|  Date - Now = 1 days' => [$nextTuesday . '14:31:00', 1, 'Tomorrow'],
            // innext 5 days
                '3|  Date - Now = 4 days' => [$nextSaturday . '14:29:00', 4, 'Saturday'],
                '4|  Date - Now = 5 days' => [$nextSaturday . '14:31:00', 5, 'Saturday'],
            // in next 8 days
                '5|  Date - Now = 5 days' => [$nextSunday . '14:29:00', 5, 'Next Sunday'],
                '6|  Date - Now = 6 days' => [$nextSunday . '14:31:00', 6, 'Next Sunday'],

                '9|  Date - Now = 7 days' => [$nextNextTuesday . '14:29:00', 7, 'Next Tuesday'],
                '10| Date - Now = 8 days' => [$nextNextTuesday . '14:31:00', 8, 'Next Tuesday'],
            // rest
                '11| Date - Now = 8 days' => [$nextNextWednesday . '14:29:00', 8, 'Wed 30 May 2018'],
                '12| Date - Now = 9 days' => [$nextNextWednesday . '14:31:00', 9, 'Wed 30 May 2018'],
        ];
    }
}
