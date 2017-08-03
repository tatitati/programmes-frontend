<?php
declare(strict_types = 1);
namespace Tests\App\ValueObject;

use App\ValueObject\BroadcastDay;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\Enumeration\NetworkMediumEnum;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Sid;
use Cake\Chronos\Chronos;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BroadcastDayTest extends TestCase
{
    public function testTvDay()
    {
        $day = new BroadcastDay(
            new Chronos('2017-10-10 20:00:00Z'),
            NetworkMediumEnum::TV
        );

        $this->assertEquals(new Chronos('2017-10-10 06:00:00'), $day->start());
        $this->assertEquals(new Chronos('2017-10-11 06:00:00'), $day->end());
    }

    public function testTvDayEarlyMorning()
    {
        $day = new BroadcastDay(
            new Chronos('2017-10-10 03:00:00Z'),
            NetworkMediumEnum::TV
        );

        $this->assertEquals(new Chronos('2017-10-09 06:00:00'), $day->start());
        $this->assertEquals(new Chronos('2017-10-10 06:00:00'), $day->end());
    }

    public function testRadioDay()
    {
        $day = new BroadcastDay(
            new Chronos('2017-10-10 20:00:00Z'),
            NetworkMediumEnum::RADIO
        );

        $this->assertEquals(new Chronos('2017-10-10 00:00:00'), $day->start());
        $this->assertEquals(new Chronos('2017-10-11 06:00:00'), $day->end());
    }

    public function testRadioDayEarlyMorning()
    {
        $day = new BroadcastDay(
            new Chronos('2017-10-10 03:00:00Z'),
            NetworkMediumEnum::RADIO
        );

        $this->assertEquals(new Chronos('2017-10-10 00:00:00'), $day->start());
        $this->assertEquals(new Chronos('2017-10-11 06:00:00'), $day->end());
    }

    public function testBadNetworkMedium()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Called new BroadcastDay() with an invalid networkMedium. Expected one of "radio", "tv", "" but got "garbage"'
        );

        new BroadcastDay(new Chronos('2017-10-10 03:00:00Z'), 'garbage');
    }

    public function testActiveOnWholeDay()
    {
        $service = $this->createService('2017-01-01 00:00:00', '2017-01-03 23:23:59');
        $date = Chronos::create(2017, 1, 2, 9);
        $broadcastDay = new BroadcastDay($date, NetworkMediumEnum::TV);

        $this->assertTrue($broadcastDay->serviceIsActiveOnThisDay($service));
    }

    public function testActiveOnStartOfDay()
    {
        $service = $this->createService('2017-01-01 13:00:00', '2017-01-03 23:23:59');
        $date = Chronos::create(2017, 1, 2, 9);
        $broadcastDay = new BroadcastDay($date, NetworkMediumEnum::TV);

        $this->assertTrue($broadcastDay->serviceIsActiveOnThisDay($service));
    }

    public function testActiveOnEndOfDay()
    {
        $service = $this->createService('2017-01-01 13:00:00', '2017-01-02 08:00:00');
        $date = Chronos::create(2017, 1, 2, 9);
        $broadcastDay = new BroadcastDay($date, NetworkMediumEnum::TV);

        $this->assertTrue($broadcastDay->serviceIsActiveOnThisDay($service));
    }

    public function testActiveOnNoServiceStart()
    {
        $service = $this->createService(null, '2017-01-03 23:23:59');
        $date = Chronos::create(2017, 1, 2, 9);
        $broadcastDay = new BroadcastDay($date, NetworkMediumEnum::TV);

        $this->assertTrue($broadcastDay->serviceIsActiveOnThisDay($service));
    }

    public function testActiveOnNoServiceEnd()
    {
        $service = $this->createService('2017-01-01 00:00:00', null);
        $date = Chronos::create(2017, 1, 2, 9);
        $broadcastDay = new BroadcastDay($date, NetworkMediumEnum::TV);

        $this->assertTrue($broadcastDay->serviceIsActiveOnThisDay($service));
    }

    public function testNotActiveOn()
    {
        $service = $this->createService('2017-01-01 09:00:00', '2017-01-03 23:23:59');
        $date = Chronos::create(2016, 1, 2, 9);
        $broadcastDay = new BroadcastDay($date, NetworkMediumEnum::TV);

        $this->assertFalse($broadcastDay->serviceIsActiveOnThisDay($service));
    }

    public function testNotActiveOnOnlyServiceStart()
    {
        $service = $this->createService('2017-01-01 00:00:00', null);
        $date = Chronos::create(2016, 1, 2, 9);
        $broadcastDay = new BroadcastDay($date, NetworkMediumEnum::TV);

        $this->assertFalse($broadcastDay->serviceIsActiveOnThisDay($service));
    }

    public function testNotActiveOnOnlyServiceEnd()
    {
        $service = $this->createService(null, '2017-01-03 23:23:59');
        $date = Chronos::create(2018, 1, 2, 9);
        $broadcastDay = new BroadcastDay($date, NetworkMediumEnum::TV);

        $this->assertFalse($broadcastDay->serviceIsActiveOnThisDay($service));
    }

    private function createService(?string $startDateTime, ?string $endDateTime): Service
    {
        $start = is_null($startDateTime) ? null : Chronos::createFromFormat('Y-m-d H:i:s', $startDateTime);
        $end = is_null($endDateTime) ? null : Chronos::createFromFormat('Y-m-d H:i:s', $endDateTime);
        return new Service(1, new Sid('sid'), new Pid('bcdfghjk'), 'title', 'shortName', 'urlKey', null, $start, $end);
    }
}
