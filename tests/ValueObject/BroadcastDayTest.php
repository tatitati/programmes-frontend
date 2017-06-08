<?php
declare(strict_types = 1);
namespace Tests\App\ValueObject;

use App\ValueObject\BroadcastDay;
use BBC\ProgrammesPagesService\Domain\Enumeration\NetworkMediumEnum;
use Cake\Chronos\Chronos;
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
}
