<?php
declare(strict_types = 1);
namespace Tests\App\Ds2013\Organism\Broadcast;

use App\Ds2013\Organism\Broadcast\BroadcastPresenter;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;

class BroadcastPresenterTest extends TestCase
{
    /**
     * @dataProvider datesProvider
     */
    public function testClassesBasedOnStatusBroadcast($start, $end, $isOnAir)
    {
        // set now
        ApplicationTime::setTime((new Chronos('2017-05-18 08:30:00'))->getTimestamp());

        $broadcast = new Broadcast(
            new Pid('b1234567'),
            $this->createMock(Version::class),
            $this->createMock(Episode::class),
            $this->createMock(Service::class),
            new Chronos($start),
            new Chronos($end),
            2700,
            false,
            false
        );

        $presenter = new BroadcastPresenter($broadcast);

        $this->assertEquals($isOnAir, $presenter->isOnAirNow());
    }

    public function datesProvider()
    {
        return [
            ['2017-05-18 07:00:00', '2017-05-18 08:00:00', false],
            ['2017-05-18 08:00:00', '2017-05-18 09:00:00', true],
        ];
    }
}
