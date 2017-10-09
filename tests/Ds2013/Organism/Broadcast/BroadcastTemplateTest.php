<?php
declare(strict_types = 1);
namespace Tests\App\Ds2013\Organism\Broadcast;

use App\Ds2013\Organism\Broadcast\BroadcastPresenter;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\BroadcastGap;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Cake\Chronos\Chronos;
use Tests\App\BaseTemplateTestCase;

class BroadcastTemplateTest extends BaseTemplateTestCase
{
    public function testBroadcast()
    {
        $crawler = $this->presenterCrawler(new BroadcastPresenter(
            $this->broadcast(
                new Chronos('2015-01-01 06:00:00Z'),
                new Chronos('2015-01-01 07:00:00Z')
            )
        ));

        // No 'On air' box
        $this->assertCount(0, $crawler->filter(".broadcast__live"));

        // Contains a programme
        $this->assertCount(1, $crawler->filter(".broadcast__programme .programme"));

        // Schema.org properties
        $broadcast = $crawler->filter(".broadcast");
        $this->assertSchemaOrgItem('BroadcastEvent', $broadcast);
        $this->assertSchemaOrgPropertyAttr('2015-01-01T06:00:00+00:00', $broadcast, 'startDate');
        $this->assertSchemaOrgPropertyAttr('2015-01-01T07:00:00+00:00', $broadcast, 'endDate');
    }

    public function testBroadcastOnNow()
    {
        ApplicationTime::setTime((new Chronos('2015-01-01 06:30:00Z'))->timestamp);

        $crawler = $this->presenterCrawler(new BroadcastPresenter(
            $this->broadcast(
                new Chronos('2015-01-01 06:00:00Z'),
                new Chronos('2015-01-01 07:00:00Z')
            )
        ));

        // 'On air' box
        $this->assertEquals('On air', $crawler->filter(".broadcast__live")->text());

        // Contains a programme
        $this->assertCount(1, $crawler->filter(".broadcast__programme .programme"));
    }

    public function testBroadcastGap()
    {
        $mockService = $this->createMock(Service::class);
        $mockService->method('getPid')->willReturn(new Pid('b0000002'));

        $crawler = $this->presenterCrawler(new BroadcastPresenter(
            new BroadcastGap(
                $mockService,
                new Chronos('2015-01-01 06:00:00Z'),
                new Chronos('2015-01-01 07:00:00Z')
            )
        ));

        $this->assertEquals('Off air', $crawler->filter(".broadcast__live")->text());
        $this->assertEquals('Programmes will resume at 07:00', $crawler->filter(".broadcast__programme")->text());
    }

    private function broadcast(Chronos $startDate, Chronos $endDate): Broadcast
    {
        $mockEpisode = $this->createMock(Episode::class);
        // I'm kind of amazed this primitive mock doesnt blow up the programme object
        // but we'll keep it for now
        $mockEpisode->method('getPid')->willReturn(new Pid('b0000001'));
        $mockEpisode->method('getTitle')->willReturn('KHAAAAAN');
        $mockEpisode->method('getAncestry')->willReturn([$mockEpisode]);

        $mockService = $this->createMock(Service::class);
        $mockService->method('getPid')->willReturn(new Pid('b0000002'));

        return new Broadcast(
            new Pid('b1234567'),
            $this->createMock(Version::class),
            $mockEpisode,
            $mockService,
            $startDate,
            $endDate,
            2700,
            false,
            false
        );
    }

    protected function tearDown()
    {
        ApplicationTime::blank();
    }
}
