<?php
declare(strict_types = 1);

namespace Tests\App\DataFixtures\PagesService;

use App\Builders\MusicSegmentBuilder;
use App\Builders\SegmentBuilder;
use App\Builders\SegmentEventBuilder;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;

class SegmentEventsFixture
{
    public static function wordsAndMusicAvailable(): array
    {
        $segmentEvents = [];
        $segmentEvents[] = SegmentEventBuilder::any()->with([
            'pid' => new Pid('st000001'),
            'version' => VersionsFixture::wordsAndMusicAvailable(),
            'segment' => MusicSegmentBuilder::any()->with([
                'dbId' => 234,
                'pid' => new Pid('s0010001'),
                'type' => 'music',
                'synopses' => new Synopses('', '', ''),
                'contributionsCount' => 2,
                'title' => 'Fugue No.1 in C Major',
                'duration' => 149,
                'contributions' => null,
                'musicRecordId' => 'n534j3',
                'releaseTitle' => null,
                'catalogueNumber' => null,
                'recordLabel' => 'Decca 4660662',
                'publisher' => null,
                'trackNumber' => 'Tr2',
                'trackSide' => null,
                'sourceMedia' => null,
                'musicCode' => null,
                'recordingDate' => null,
            ])->build(),
            'title' => null,
            'isChapter' => false,
            'offset' => 0,
            'position' => 1,
        ])->build();

        $segmentEvents[] = SegmentEventBuilder::any()->with([
            'pid' => new Pid('st000002'),
            'version' => VersionsFixture::wordsAndMusicAvailable(),
            'segment' => SegmentBuilder::any()->with([
                'dbId' => 569,
                'pid' => new Pid('s0010002'),
                'type' => 'speech',
            ])->build(),
            'title' => null,
            'isChapter' => false,
            'offset' => 150,
            'position' => 2,
        ])->build();

        $segmentEvents[] = SegmentEventBuilder::any()->with([
            'pid' => new Pid('st000003'),
            'version' => VersionsFixture::wordsAndMusicAvailable(),
            'segment' => MusicSegmentBuilder::any()->with([
                'dbId' => 5677,
                'pid' => new Pid('s0010003'),
                'type' => 'music',
                'synopses' => new Synopses('', '', ''),
                'contributionsCount' => 2,
                'title' => 'Etude in G Flat major, Op.10, No.5 ("Black Key")',
                'duration' => 92,
                'contributions' => null,
                'musicRecordId' => 'n534j5',
                'releaseTitle' => null,
                'catalogueNumber' => null,
                'recordLabel' => 'Naxos Historical 8110606',
                'publisher' => null,
                'trackNumber' => 'Tr11',
                'trackSide' => null,
                'sourceMedia' => null,
                'musicCode' => null,
                'recordingDate' => null,
            ])->build(),
            'title' => null,
            'isChapter' => false,
            'offset' => 160,
            'position' => 3,
        ])->build();

        return $segmentEvents;
    }

    public static function eastendersEpisodeAvailable(): array
    {
        $segmentEvents = [];
        $segmentEvents[] = SegmentEventBuilder::any()->with([
            'pid' => new Pid('st000005'),
            'version' => VersionsFixture::eastendersEpisodeAvailable(),
            'segment' => SegmentBuilder::any()->with([
                'dbId' => 588,
                'pid' => new Pid('s0010003'),
                'type' => 'chapter',
                'synopses' => new Synopses('', '', ''),
                'title' => 'Segment 1',
                'duration' => 150,
            ])->build(),
            'title' => null,
            'isChapter' => true,
            'offset' => 150,
            'position' => 1,
        ])->build();

        $segmentEvents[] = SegmentEventBuilder::any()->with([
            'pid' => new Pid('st000005'),
            'version' => VersionsFixture::eastendersEpisodeAvailable(),
            'segment' => SegmentBuilder::any()->with([
                'dbId' => 588,
                'pid' => new Pid('s0010004'),
                'type' => 'chapter',
                'synopses' => new Synopses('The revenge', '', ''),
                'title' => 'Segment 2',
                'duration' => 200,
            ])->build(),
            'title' => null,
            'isChapter' => true,
            'offset' => 300,
            'position' => 2,
        ])->build();

        return $segmentEvents;
    }
}
