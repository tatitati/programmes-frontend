<?php
declare(strict_types = 1);

namespace Tests\App\ValueObject;

use App\ValueObject\BroadcastWeek;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Sid;
use Cake\Chronos\Chronos;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BroadcastWeekTest extends TestCase
{
    public function testFirstWeekIsPartOfThePreviousYear()
    {
        $week = new BroadcastWeek('2009-w01');

        $this->assertEquals('2008-12-29 00:00:00', $week->start()->toDateTimeString());
        $this->assertEquals('2009-01-04 23:59:59', $week->end()->toDateTimeString());
    }

    public function testFirstWeek()
    {
        $week = new BroadcastWeek('2017-w01');

        $this->assertEquals('2017-01-02 00:00:00', $week->start()->toDateTimeString());
        $this->assertEquals('2017-01-08 23:59:59', $week->end()->toDateTimeString());
    }

    public function testLastWeek()
    {
        $week = new BroadcastWeek('2018-w53');

        $this->assertEquals('2018-12-31 00:00:00', $week->start()->toDateTimeString());
        $this->assertEquals('2019-01-06 23:59:59', $week->end()->toDateTimeString());
    }

    public function testWeekTooHighForYear()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            '2017 does not have enough weeks in it.'
        );

        new BroadcastWeek('2017-w53');
    }

    public function testServiceIsActiveInThisPeriodWithin()
    {
        $week = new BroadcastWeek('2017-w02');

        $service = $this->createService('2017-01-10 00:00:00', '2017-01-11 00:00:00');
        $this->assertTrue($week->serviceIsActiveInThisPeriod($service));
    }

    public function testServiceIsActiveInThisPeriodOutside()
    {
        $week = new BroadcastWeek('2017-w02');

        $service = $this->createService('2017-01-7 00:00:00', '2017-01-17 00:00:00');
        $this->assertTrue($week->serviceIsActiveInThisPeriod($service));
    }

    public function testServiceIsActiveInThisPeriodAtStart()
    {
        $week = new BroadcastWeek('2017-w02');

        $service = $this->createService('2017-01-07 00:00:00', '2017-01-10 00:00:00');
        $this->assertTrue($week->serviceIsActiveInThisPeriod($service));
    }

    public function testServiceIsActiveInThisPeriodAtEnd()
    {
        $week = new BroadcastWeek('2017-w02');

        $service = $this->createService('2017-01-10 00:00:00', '2017-01-17 00:00:00');
        $this->assertTrue($week->serviceIsActiveInThisPeriod($service));
    }

    public function testServiceIsNotActiveInThisPeriod()
    {
        $week = new BroadcastWeek('2017-w02');

        $service = $this->createService('2017-01-01 00:00:00', '2017-01-07 00:00:00');
        $this->assertFalse($week->serviceIsActiveInThisPeriod($service));

        $service = $this->createService('2017-01-17 00:00:00', '2017-01-19 00:00:00');
        $this->assertFalse($week->serviceIsActiveInThisPeriod($service));
    }

    private function createService(?string $startDateTime, ?string $endDateTime): Service
    {
        $start = is_null($startDateTime) ? null : Chronos::createFromFormat('Y-m-d H:i:s', $startDateTime);
        $end = is_null($endDateTime) ? null : Chronos::createFromFormat('Y-m-d H:i:s', $endDateTime);
        return new Service(1, new Sid('sid'), new Pid('bcdfghjk'), 'title', 'shortName', 'urlKey', null, $start, $end);
    }
}
