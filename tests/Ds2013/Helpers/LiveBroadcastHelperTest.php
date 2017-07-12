<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Helpers;

use App\Ds2013\Helpers\LiveBroadcastHelper;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\ValueObject\Sid;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class LiveBroadcastHelperTest extends TestCase
{
    /** @var LiveBroadcastHelper */
    private $helper;

    public function setUp()
    {
        $this->helper = new LiveBroadcastHelper();
        ApplicationTime::setTime((new Chronos('2017-06-01 00:00:00'))->getTimestamp());
    }

    public function testIsWatchableTrue()
    {
        $collapsedBroadcast = $this->createCollapsedBroadcast(
            ['bbc_one_london', 'bbc_one_cambridge'],
            new DateTimeImmutable('2017-05-31 23:00:00'),
            new DateTimeImmutable('2017-06-01 01:00:00')
        );
        $result = $this->helper->isWatchableLive($collapsedBroadcast);
        $this->assertTrue($result);
    }

    public function testIsWatchableLiveRespectsBlanked()
    {
        $collapsedBroadcast = $this->createCollapsedBroadcast(
            ['bbc_one_london', 'bbc_one_cambridge'],
            new DateTimeImmutable('2017-05-31 23:00:00'),
            new DateTimeImmutable('2017-06-01 01:00:00'),
            true
        );
        $result = $this->helper->isWatchableLive($collapsedBroadcast);
        $this->assertFalse($result);
    }

    public function testIsWatchableWrongTime()
    {
        $collapsedBroadcast = $this->createCollapsedBroadcast(
            ['bbc_one_london', 'bbc_one_cambridge'],
            new DateTimeImmutable('2017-06-01 00:00:01'),
            new DateTimeImmutable('2017-06-01 01:00:00')
        );
        $result = $this->helper->isWatchableLive($collapsedBroadcast);
        $this->assertFalse($result);
    }

    public function testIsWatchableWrongService()
    {
        $collapsedBroadcast = $this->createCollapsedBroadcast(
            ['russia_today'],
            new DateTimeImmutable('2017-05-31 23:00:00'),
            new DateTimeImmutable('2017-06-01 01:00:00')
        );
        $result = $this->helper->isWatchableLive($collapsedBroadcast);
        $this->assertFalse($result);
    }

    public function testIsWatchableAdvancedLive()
    {
        $collapsedBroadcast = $this->createCollapsedBroadcast(
            ['bbc_one_london', 'bbc_one_cambridge'],
            new DateTimeImmutable('2017-06-01 00:05:00'),
            new DateTimeImmutable('2017-06-01 01:00:00')
        );
        $result = $this->helper->isWatchableLive($collapsedBroadcast, true);
        $this->assertTrue($result);
    }

    public function testSimulcastUrlBasic()
    {
        $collapsedBroadcast = $this->createCollapsedBroadcast(
            ['bbc_one_london'],
            new DateTimeImmutable('2017-06-01 00:00:00'),
            new DateTimeImmutable('2017-06-01 01:00:00')
        );
        $result = $this->helper->simulcastUrl($collapsedBroadcast);
        $this->assertEquals('/iplayer/live/bbcone', $result);
    }

    public function testSimulcastUrlOrdering()
    {
        $collapsedBroadcast = $this->createCollapsedBroadcast(
            ['bbc_news24', 'bbc_parliament', 'bbc_one_london'],
            new DateTimeImmutable('2017-06-01 00:00:00'),
            new DateTimeImmutable('2017-06-01 01:00:00')
        );
        $result = $this->helper->simulcastUrl($collapsedBroadcast);
        $this->assertEquals('/iplayer/live/bbcone', $result);
    }

    public function testSimulcastUrlPreferredService()
    {
        $collapsedBroadcast = $this->createCollapsedBroadcast(
            ['bbc_news24', 'bbc_parliament', 'bbc_one_london'],
            new DateTimeImmutable('2017-06-01 00:00:00'),
            new DateTimeImmutable('2017-06-01 01:00:00')
        );
        $preferredService = $this->createMock(Service::class);
        $preferredService->method('getSid')->willReturn(new Sid('bbc_news24'));
        $result = $this->helper->simulcastUrl($collapsedBroadcast, $preferredService);
        $this->assertEquals('/iplayer/live/bbcnews', $result);
    }

    private function createCollapsedBroadcast(
        array $sids,
        DateTimeImmutable $start,
        DateTimeImmutable $end,
        bool $isBlanked = false
    ): CollapsedBroadcast {
        $programmeItem = $this->createMock(ProgrammeItem::class);
        $services = [];
        foreach ($sids as $sid) {
            $service = $this->createMock(Service::class);
            $sidObject = new Sid($sid);
            $service->method('getSid')->willReturn($sidObject);
            $services[] = $service;
        }
        return new CollapsedBroadcast(
            $programmeItem,
            $services,
            $start,
            $end,
            30,
            $isBlanked
        );
    }
}
