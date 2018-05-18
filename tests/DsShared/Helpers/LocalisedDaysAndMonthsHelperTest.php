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
        ApplicationTime::setTime((new Chronos('2017-7-21 12:00:00'))->timestamp);
        $this->helper = new LocalisedDaysAndMonthsHelper(new TranslateProvider(new TranslateFactory()));
    }

    public function teardown()
    {
        ApplicationTime::blank();
    }

    /** @dataProvider getFormatedDayProvider */
    public function testGetFormatedDay(Chronos $date, string $expected)
    {
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
}
