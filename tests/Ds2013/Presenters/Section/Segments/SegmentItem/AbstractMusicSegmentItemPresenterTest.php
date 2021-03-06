<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Presenters\Section\Segments\SegmentItem;

use App\Builders\CollapsedBroadcastBuilder;
use App\Builders\ContributionBuilder;
use App\Builders\ContributorBuilder;
use App\Builders\MusicSegmentBuilder;
use App\Builders\SegmentBuilder;
use App\Builders\SegmentEventBuilder;
use App\Ds2013\Presenters\Section\Segments\SegmentItem\AbstractMusicSegmentItemPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractMusicSegmentItemPresenterTest extends TestCase
{
    public function tearDown()
    {
        Chronos::setTestNow();
    }

    /** @dataProvider hasTimingDataProvider */
    public function testHasTiming(bool $expected, ?int $offset, string $timingType)
    {
        $segmentEvent = SegmentEventBuilder::any()->with(['offset' => $offset])->build();
        $stub = $this->getMockForAbstractClass(AbstractMusicSegmentItemPresenter::class, [$segmentEvent, $timingType, null]);

        $this->assertSame($expected, $stub->hasTiming());
    }

    public function hasTimingDataProvider(): array
    {
        return [
            'no-offset-unacceptable-timing-type' => [false, null, 'random'],
            'no-offset-acceptable-timing-type' => [false, null, AbstractMusicSegmentItemPresenter::TIMING_POST],
            'offset-unacceptable-timing-type' => [false, 1, AbstractMusicSegmentItemPresenter::TIMING_OFF],
            'offset-acceptable-timing-type' => [true, 1, AbstractMusicSegmentItemPresenter::TIMING_POST],
            'no-offset-post-timing-type' => [true, 1, AbstractMusicSegmentItemPresenter::TIMING_POST],
            'no-offset-pre-timing-type' => [true, 1, AbstractMusicSegmentItemPresenter::TIMING_PRE],
            'no-offset-during-timing-type' => [true, 1, AbstractMusicSegmentItemPresenter::TIMING_DURING],
        ];
    }

    public function testTiming()
    {
        $segmentEvent = SegmentEventBuilder::any()->build();
        $stub = $this->getPresenter($segmentEvent, 'anything', null, false);

        $this->assertNull($stub->getTiming());
    }

    public function testTimingPre()
    {
        $start = Chronos::now()->setTime(8, 0, 0);
        $collapsedBroadcast = CollapsedBroadcastBuilder::any()->with(['startAt' => $start])->build();
        $segmentEvent = SegmentEventBuilder::any()->with(['offset' => 359])->build();
        $timingType = AbstractMusicSegmentItemPresenter::TIMING_PRE;
        $stub = $this->getPresenter($segmentEvent, $timingType, $collapsedBroadcast);

        $this->assertSame('08:05', $stub->getTiming());
    }

    public function testTimingPost()
    {
        $segmentEvent = SegmentEventBuilder::any()->with(['offset' => 3959])->build();
        $timingType = AbstractMusicSegmentItemPresenter::TIMING_POST;
        $stub = $this->getPresenter($segmentEvent, $timingType, null);

        $this->assertSame('01:05', $stub->getTiming());
    }

    public function testTimingDuringInTheFuture()
    {
        $start = Chronos::now()->setTime(8, 0, 0);
        Chronos::setTestNow($start->setTime(8, 12, 0));
        $collapsedBroadcast = CollapsedBroadcastBuilder::any()->with(['startAt' => $start])->build();
        $segment = MusicSegmentBuilder::any()->with(['duration' => 300])->build();
        $segmentEvent = SegmentEventBuilder::any()->with(['offset' => 600, 'segment' => $segment])->build();
        $timingType = AbstractMusicSegmentItemPresenter::TIMING_DURING;
        $stub = $this->getPresenter($segmentEvent, $timingType, $collapsedBroadcast);

        $this->assertSame('Now', $stub->getTiming());
    }

    public function testTimingDuring()
    {
        $start = Chronos::now()->setTime(8, 0, 0);
        Chronos::setTestNow($start->setTime(8, 19, 0));
        $collapsedBroadcast = CollapsedBroadcastBuilder::any()->with(['startAt' => $start])->build();
        $segment = MusicSegmentBuilder::any()->with(['duration' => 300])->build();
        $segmentEvent = SegmentEventBuilder::any()->with(['offset' => 600, 'segment' => $segment])->build();
        $timingType = AbstractMusicSegmentItemPresenter::TIMING_DURING;
        $stub = $this->getPresenter($segmentEvent, $timingType, $collapsedBroadcast);

        $this->assertSame('4 minutes ago', $stub->getTiming());
    }

    public function testRecordIdImage()
    {
        $segment = MusicSegmentBuilder::any()->with(['musicRecordId' => 'recordId'])->build();
        $segmentEvent = SegmentEventBuilder::any()->with(['segment' => $segment])->build();
        $stub = $this->getPresenter($segmentEvent, 'anything', null);

        $this->assertSame('https://www.bbc.co.uk/music/images/records/96x96/recordId', $stub->getImageUrl());
    }

    public function testMusicBrainzImage()
    {
        $contributor = ContributorBuilder::any()->with(['musicBrainzId' => 'something'])->build();
        $contribution = ContributionBuilder::any()->with(['contributor' => $contributor])->build();
        $segmentEvent = SegmentEventBuilder::any()->build();
        // @TODO can I refactor the below?
        $stub = $this->getPresenter($segmentEvent, 'anything', null);
        $stub->expects($this->any())
            ->method('getPrimaryContribution')
            ->willReturn($contribution);

        $this->assertSame('https://ichef.bbci.co.uk/music/images/artists/96x96/something.jpg', $stub->getImageUrl());
    }

    public function testDefaultImage()
    {
        $segmentEvent = SegmentEventBuilder::any()->build();
        $stub = $this->getPresenter($segmentEvent, 'anything', null);
        $stub->expects($this->any())
            ->method('getPrimaryContribution')
            ->willReturn(null);

        $this->assertSame('https://ichef.bbci.co.uk/images/ic/96x96/p01c9cjb.png', $stub->getImageUrl());
    }

    public function testGetFavouriteButtonTitle()
    {
        $contributor1 = ContributorBuilder::any()->with(['musicBrainzId' => 'something', 'name' => 'ContributorName1'])->build();
        $contribution1 = ContributionBuilder::any()->with(['contributor' => $contributor1])->build();

        $contributor2 = ContributorBuilder::any()->with(['musicBrainzId' => 'something', 'name' => 'ContributorName2'])->build();
        $contribution2 = ContributionBuilder::any()->with(['contributor' => $contributor2])->build();

        $vsContributor1 = ContributorBuilder::any()->with(['musicBrainzId' => 'something', 'name' => 'VsContributorName1'])->build();
        $vsContribution1 = ContributionBuilder::any()->with(['contributor' => $vsContributor1])->build();

        $vsContributor2 = ContributorBuilder::any()->with(['musicBrainzId' => 'something', 'name' => 'VsContributorName2'])->build();
        $vsContribution2 = ContributionBuilder::any()->with(['contributor' => $vsContributor2])->build();

        $segment = SegmentBuilder::any()->with(['contributions' => [$contribution1], 'title' => 'SegmentTitle'])->build();
        $segmentEvent = SegmentEventBuilder::any()->with(['segment' => $segment])->build();
        $stub = $this->getMockForAbstractClass(
            AbstractMusicSegmentItemPresenter::class,
            [$segmentEvent, 'anything', null],
            '',
            true,
            true,
            true,
            ['hasTiming', 'getPrimaryContributions', 'getVersusContributions', 'getFeaturedContributions']
        );

        $stub->expects($this->any())
            ->method('getPrimaryContributions')
            ->willReturn([$contribution1, $contribution2]);
        $stub->expects($this->any())
            ->method('getVersusContributions')
            ->willReturn([$vsContribution1, $vsContribution2]);
        $stub->expects($this->any())
            ->method('getFeaturedContributions')
            ->willReturn([$contribution1, $contribution2]);

        $this->assertEquals(
            'ContributorName1 & ContributorName2 vs VsContributorName1 & VsContributorName2 || SegmentTitle (feat. ContributorName1 & ContributorName2)',
            $stub->getFavouriteButtonTitle()
        );
    }

    /**
     * @param SegmentEvent $segmentEvent
     * @param string $timingType
     * @param CollapsedBroadcast|null $collapsedBroadcast
     * @param bool $hasTiming
     * @return AbstractMusicSegmentItemPresenter|MockObject
     */
    private function getPresenter(SegmentEvent $segmentEvent, string $timingType, ?CollapsedBroadcast $collapsedBroadcast, bool $hasTiming = true)
    {
        $stub = $this->getMockForAbstractClass(AbstractMusicSegmentItemPresenter::class, [$segmentEvent, $timingType, $collapsedBroadcast], '', true, true, true, ['hasTiming', 'getPrimaryContribution']);
        $stub->expects($this->any())
            ->method('hasTiming')
            ->willReturn($hasTiming);

        return $stub;
    }
}
