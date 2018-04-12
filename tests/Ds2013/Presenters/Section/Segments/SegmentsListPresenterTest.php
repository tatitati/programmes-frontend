<?php
declare(strict_types=1);

namespace Tests\App\Ds2013\Presenters\Section\Segments;

use App\Ds2013\Presenters\Section\Segments\SegmentItem\AbstractMusicSegmentItemPresenter;
use App\Ds2013\Presenters\Section\Segments\SegmentItem\GroupPresenter;
use App\Ds2013\Presenters\Section\Segments\SegmentItem\ClassicalMusicPresenter;
use App\Ds2013\Presenters\Section\Segments\SegmentItem\SpeechPresenter;
use App\Ds2013\Presenters\Section\Segments\SegmentsListPresenter;
use App\DsShared\Helpers\LiveBroadcastHelper;
use App\DsShared\Helpers\PlayTranslationsHelper;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\MusicSegment;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SegmentsListPresenterTest extends TestCase
{
    /** @var LiveBroadcastHelper|MockObject */
    private $mockLiveBroadcastHelper;

    /** @var PlayTranslationsHelper|MockObject */
    private $mockPlayTranslationsHelper;

    public function setUp()
    {
        $this->mockPlayTranslationsHelper = $this->createMock(PlayTranslationsHelper::class);
        $this->mockLiveBroadcastHelper = $this->createMock(LiveBroadcastHelper::class);
        ApplicationTime::setTime((new Chronos('2017-06-01 12:00:00'))->getTimestamp());
    }

    /** @dataProvider getTitleProvider */
    public function testGetTitle(string $expected, array $segmentEvents)
    {
        $episode = $this->createMock(Episode::class);
        $presenter = new SegmentsListPresenter(
            $this->mockLiveBroadcastHelper,
            $this->mockPlayTranslationsHelper,
            $episode,
            $segmentEvents,
            null,
            null,
            []
        );
        $this->assertEquals($expected, $presenter->getTitle());
    }

    public function getTitleProvider(): array
    {
        $musicSegment = $this->createConfiguredMock(
            MusicSegment::class,
            [
                'getContributions' =>
                    [
                        $this->createConfiguredMock(Contribution::class, ['getCreditRole' => 'composer']),
                    ],
            ]
        );
        $chapterSegment = $this->createConfiguredMock(Segment::class, ['getType' => 'chapter']);
        $highlightSegment = $this->createConfiguredMock(Segment::class, ['getType' => 'highlight']);
        $speechSegment = $this->createConfiguredMock(Segment::class, ['getType' => 'speech']);

        $musicSegmentEvent = $this->createConfiguredMock(SegmentEvent::class, ['getSegment' => $musicSegment]);
        $chapterSegmentEvent = $this->createConfiguredMock(SegmentEvent::class, ['getSegment' => $chapterSegment]);
        $highlightSegmentEvent = $this->createConfiguredMock(SegmentEvent::class, ['getSegment' => $highlightSegment]);
        $speechSegmentEvent = $this->createConfiguredMock(SegmentEvent::class, ['getSegment' => $speechSegment]);

        return [
            'only music segments' => ['music_played', [$musicSegmentEvent, $highlightSegmentEvent, $speechSegmentEvent]],
            'music and chapter segments' => ['music_and_featured', [$musicSegmentEvent, $chapterSegmentEvent]],
            'only chapter segments' => ['chapters', [$chapterSegmentEvent]],
            'other types of segments' => ['featured', [$highlightSegmentEvent, $speechSegmentEvent]],
            'chapter has preference over non-music segments' => ['chapters', [$chapterSegmentEvent, $highlightSegmentEvent, $speechSegmentEvent]],
            'music has preference over all types' => ['music_played', [$musicSegmentEvent, $highlightSegmentEvent, $speechSegmentEvent]],
            'music, chapter and others segments' => ['music_played', [$musicSegmentEvent, $highlightSegmentEvent, $speechSegmentEvent]],
        ];
    }

    /** @dataProvider getMorelessClassProvider */
    public function testGetMorelessClass(string $expected, array $segmentEvents, ProgrammeItem $context)
    {
        $presenter = new SegmentsListPresenter(
            $this->mockLiveBroadcastHelper,
            $this->mockPlayTranslationsHelper,
            $context,
            $segmentEvents,
            null,
            null,
            []
        );
        $this->assertEquals($expected, $presenter->getMorelessClass());
    }

    public function getMorelessClassProvider(): array
    {
        $seg1 = $this->createMock(SegmentEvent::class);
        $seg2 = $this->createMock(SegmentEvent::class);
        $seg3 = $this->createMock(SegmentEvent::class);
        $seg4 = $this->createMock(SegmentEvent::class);
        $seg5 = $this->createMock(SegmentEvent::class);
        $seg6 = $this->createMock(SegmentEvent::class);
        $seg7 = $this->createMock(SegmentEvent::class);

        $sixSegmentEvents = [$seg1, $seg2, $seg3, $seg4, $seg5, $seg6];
        $sevenSegmentEvents = [$seg1, $seg2, $seg3, $seg4, $seg5, $seg6, $seg7];
        $fiveSegmentEvents = [$seg1, $seg2, $seg3, $seg4, $seg5];

        $radioContext = $this->createConfiguredMock(ProgrammeItem::class, ['isRadio' => true]);
        $nonRadioContext = $this->createConfiguredMock(ProgrammeItem::class, ['isRadio' => false]);

        return [
            'radio context with more than 6 segments' => ['ml@bpb1', $sevenSegmentEvents, $radioContext],
            'non radio context with more than 6 segments' => ['ml@bpb1', $sevenSegmentEvents, $nonRadioContext],
            'radio context with exactly 6 segments' => ['ml@bpb1', $sixSegmentEvents, $radioContext],
            'non radio context with exactly 6 segments' => ['ml@bpb1', $sixSegmentEvents, $nonRadioContext],
            'radio context with less than 6 segments' => ['', $fiveSegmentEvents, $radioContext],
            'non radio context with less than 6 segments' => ['', $fiveSegmentEvents, $nonRadioContext],
        ];
    }

    /** @dataProvider hasTimingIntroProvider */
    public function testHasTimingIntro(bool $expected, ProgrammeItem $context)
    {
        $presenter = new SegmentsListPresenter(
            $this->mockLiveBroadcastHelper,
            $this->mockPlayTranslationsHelper,
            $context,
            [],
            null,
            null,
            []
        );
        $this->assertEquals($expected, $presenter->hasTimingIntro());
    }

    public function hasTimingIntroProvider(): array
    {
        $episodeWithTiming = $this->createMock(Episode::class);
        $episodeWithTiming->method('getOption')->withConsecutive(
            ['show_tracklist_inadvance'],
            ['show_tracklist_timings']
        )
        ->willReturn(true);

        $episodeWithoutTiming = $this->createMock(Episode::class);
        $episodeWithoutTiming->method('getOption')->withConsecutive(
            ['show_tracklist_inadvance'],
            ['show_tracklist_timings']
        )
        ->willReturn(false);

        $clip = $this->createMock(Clip::class);

        return [
            'clip' => [false, $clip],
            'episode with show_tracklist_timings option' => [true, $episodeWithTiming],
            'episode without show_tracklist_timings option' => [false, $episodeWithoutTiming],
        ];
    }

    /** @dataProvider getSegmentItemsPresenterGroupingByTitleProvider */
    public function testGetSegmentItemsPresenterGroupingByTitle(array $expectedPresenters, array $expectedCount, array $segmentEvents)
    {
        $context = $this->createMock(ProgrammeItem::class);
        $presenter = new SegmentsListPresenter(
            $this->mockLiveBroadcastHelper,
            $this->mockPlayTranslationsHelper,
            $context,
            $segmentEvents,
            null,
            null,
            []
        );

        $segmentItemsPresenters = $presenter->getSegmentItemsPresenters();
        $this->assertCount(count($expectedPresenters), $segmentItemsPresenters);

        foreach ($segmentItemsPresenters as $index => $presenter) {
            $this->assertInstanceOf($expectedPresenters[$index], $presenter);
            if ($presenter instanceof GroupPresenter) {
                $subPresenters = $presenter->getPresenters();
                $this->assertCount($expectedCount[$index], $subPresenters);
            } else {
                $this->assertEquals(1, $expectedCount[$index]);
            }
        }
    }

    public function getSegmentItemsPresenterGroupingByTitleProvider(): array
    {
        $speech = $this->createConfiguredMock(Segment::class, ['getType' => 'speech']);
        $chapter = $this->createConfiguredMock(Segment::class, ['getType' => 'chapter']);
        $highlight = $this->createConfiguredMock(Segment::class, ['getType' => 'highlight']);
        $music = $this->createConfiguredMock(
            MusicSegment::class,
            [
                'getContributions' =>
                    [
                        $this->createConfiguredMock(Contribution::class, ['getCreditRole' => 'composer']),
                    ],
            ]
        );

        $seWithTitle1 = $this->createConfiguredMock(SegmentEvent::class, ['getTitle' => 'Title 1', 'getSegment' => $speech]);
        $seWithTitle2 = $this->createConfiguredMock(SegmentEvent::class, ['getTitle' => 'Title 2', 'getSegment' => $chapter]);
        $seWithTitle3 = $this->createConfiguredMock(SegmentEvent::class, ['getTitle' => 'Title 3', 'getSegment' => $highlight]);
        $seWithEmptyTitle = $this->createConfiguredMock(SegmentEvent::class, ['getTitle' => '', 'getSegment' => $speech]);
        $seWithNullTitle = $this->createConfiguredMock(SegmentEvent::class, ['getTitle' => null, 'getSegment' => $music]);

        return [
            'no segment events' => [
                [],
                [],
                [],
            ],
            'all different and non-null titles' => [
                [GroupPresenter::class, GroupPresenter::class, GroupPresenter::class],
                [1, 1, 1],
                [$seWithTitle1, $seWithTitle2, $seWithTitle3],
            ],
            'all same titles' => [
                [GroupPresenter::class],
                [3],
                [$seWithTitle1, $seWithTitle1, $seWithTitle1],
            ],
            'all null titles' => [
                [AbstractMusicSegmentItemPresenter::class, AbstractMusicSegmentItemPresenter::class, AbstractMusicSegmentItemPresenter::class],
                [1, 1, 1],
                [$seWithNullTitle, $seWithNullTitle, $seWithNullTitle],
            ],
            'all empty titles' => [
                [SpeechPresenter::class, SpeechPresenter::class, SpeechPresenter::class],
                [1, 1, 1],
                [$seWithEmptyTitle, $seWithEmptyTitle, $seWithEmptyTitle],
            ],
            'same titles interspersed with null title' => [
                [GroupPresenter::class, AbstractMusicSegmentItemPresenter::class, GroupPresenter::class],
                [2, 1, 1],
                [$seWithTitle1, $seWithTitle1, $seWithNullTitle, $seWithTitle1],
            ],
            'same titles interspersed with empty title' => [
                [GroupPresenter::class, SpeechPresenter::class, GroupPresenter::class],
                [2, 1, 1],
                [$seWithTitle1, $seWithTitle1, $seWithEmptyTitle, $seWithTitle1],
            ],
            'two groups of titles' => [
                [GroupPresenter::class, GroupPresenter::class],
                [2, 2],
                [$seWithTitle1, $seWithTitle1, $seWithTitle2, $seWithTitle2],
            ],
            'alternating two titles' => [
                [GroupPresenter::class, GroupPresenter::class, GroupPresenter::class, GroupPresenter::class],
                [1, 1, 1, 1],
                [$seWithTitle1, $seWithTitle2, $seWithTitle1, $seWithTitle2],
            ],
        ];
    }

    /** @dataProvider getSegmentItemsPresentersFilteringSegmentEventsProvider */
    public function testGetSegmentItemsPresentersFilteringSegmentEvents(
        array $expected,
        array $segmentEvents,
        ?CollapsedBroadcast $collapsedBroadcast,
        bool $showTracklistInAdvance,
        bool $isLive
    ) {
        $context = $this->createMock(ProgrammeItem::class);
        $context->expects($this->any())
            ->method('getOption')
            ->withConsecutive(['show_tracklist_inadvance'], ['show_tracklist_timings'])
            ->willReturn($showTracklistInAdvance);

        $this->mockLiveBroadcastHelper->method('isOnNowIsh')->willReturn($isLive);

        $presenter = new SegmentsListPresenter(
            $this->mockLiveBroadcastHelper,
            $this->mockPlayTranslationsHelper,
            $context,
            $segmentEvents,
            $collapsedBroadcast,
            null,
            []
        );

        // trigger filtering
        $presenter->getSegmentItemsPresenters();
        $segmentEvents = $presenter->getSegmentEvents();

        // assert that the number of groups is as expected
        $this->assertCount(count($expected), $segmentEvents);

        /** @var SegmentEvent $segmentEvent */
        foreach ($segmentEvents as $index => $segmentEvent) {
            $this->assertEquals($expected[$index], (string) $segmentEvent->getPid());
        }
    }

    public function getSegmentItemsPresentersFilteringSegmentEventsProvider(): array
    {
        $liveDebut = $this->createConfiguredMock(CollapsedBroadcast::class, [
            'isRepeat' => false,
            'getStartAt' => new Chronos('2017-06-01 11:57:00'),
        ]);

        $liveRepeat = $this->createConfiguredMock(CollapsedBroadcast::class, [
            'isRepeat' => true,
            'getStartAt' => new Chronos('2017-06-01 11:57:00'),
        ]);

        $past = $this->createConfiguredMock(CollapsedBroadcast::class, [
            'isRepeat' => true,
            'getStartAt' => new Chronos('2017-01-01 12:00:00'),
        ]);

        $musicSegment = $this->createConfiguredMock(
            MusicSegment::class,
            [
                'getContributions' =>
                    [
                        $this->createConfiguredMock(Contribution::class, ['getCreditRole' => 'composer']),
                    ],
            ]
        );
        $otherSegment = $this->createMock(Segment::class);

        $musicWithOffset1 = $this->createConfiguredMock(SegmentEvent::class, [
            'getSegment' => $musicSegment,
            'getPid' => new Pid('msc000001'),
            'getOffset' => 0,
        ]);
        $musicWithOffset2 = $this->createConfiguredMock(SegmentEvent::class, [
            'getSegment' => $musicSegment,
            'getPid' => new Pid('msc000002'),
            'getOffset' => 60,
        ]);
        $musicWithOffset3 = $this->createConfiguredMock(SegmentEvent::class, [
            'getSegment' => $musicSegment,
            'getPid' => new Pid('msc000003'),
            'getOffset' => 300,
        ]);
        $musicWithoutOffset = $this->createConfiguredMock(SegmentEvent::class, [
            'getSegment' => $musicSegment,
            'getPid' => new Pid('msc000004'),
            'getOffset' => null,
        ]);

        $otherWithOffset1 = $this->createConfiguredMock(SegmentEvent::class, [
            'getSegment' => $otherSegment,
            'getPid' => new Pid('thr000001'),
            'getOffset' => 10,
        ]);
        $otherWithOffset2 = $this->createConfiguredMock(SegmentEvent::class, [
            'getSegment' => $otherSegment,
            'getPid' => new Pid('thr000002'),
            'getOffset' => 100,
        ]);
        $otherWithOffset3 = $this->createConfiguredMock(SegmentEvent::class, [
            'getSegment' => $otherSegment,
            'getPid' => new Pid('thr000003'),
            'getOffset' => 500,
        ]);
        $otherWithoutOffset = $this->createConfiguredMock(SegmentEvent::class, [
            'getSegment' => $otherSegment,
            'getPid' => new Pid('thr000004'),
            'getOffset' => null,
        ]);

        return [
            'no segment events, no broadcast' => [
                [],
                [],
                null,
                false,
                false,
            ],
            'live debut without segment events' => [
                [],
                [],
                $liveDebut,
                false,
                true,
            ],
            'live repeat without segment events' => [
                [],
                [],
                $liveRepeat,
                false,
                true,
            ],
            'reverse array if there is music segment in live broadcast' => [
                ['msc000001', 'thr000002', 'thr000001'],
                [$otherWithOffset1, $otherWithOffset2, $musicWithOffset1],
                $liveDebut,
                false,
                true,
            ],
            'segment event outside of offset range gets cut off' => [
                ['thr000001', 'thr000002'],
                [$otherWithOffset1, $otherWithOffset2, $otherWithOffset3],
                $liveDebut,
                false,
                true,
            ],
            'segment event with without offset doesnt get cutoff' => [
                ['thr000001', 'thr000002', 'thr000004'],
                [$otherWithOffset1, $otherWithOffset2, $otherWithoutOffset],
                $liveDebut,
                false,
                true,
            ],
            'context with show_tracklist_inadvance option just returns segment event array without reversing or looking at offsets for live broadcasts' => [
                ['thr000001', 'thr000002', 'msc000001', 'thr000004'],
                [$otherWithOffset1, $otherWithOffset2, $musicWithOffset1, $otherWithoutOffset],
                $liveDebut,
                true,
                true,
            ],
            'non live music segment events dont get reversed' => [
                ['msc000001', 'msc000002', 'msc000003', 'msc000004'],
                [$musicWithOffset1, $musicWithOffset2, $musicWithOffset3, $musicWithoutOffset],
                $past,
                false,
                false,
            ],
            'repeat live music segment events dont get reversed' => [
                ['msc000001', 'msc000002', 'msc000003', 'msc000004'],
                [$musicWithOffset1, $musicWithOffset2, $musicWithOffset3, $musicWithoutOffset],
                $liveRepeat,
                false,
                true,
            ],
        ];
    }
}
