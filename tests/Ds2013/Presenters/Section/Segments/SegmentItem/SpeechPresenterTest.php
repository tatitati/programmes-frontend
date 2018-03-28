<?php
declare(strict_types=1);

namespace Tests\App\Ds2013\Presenters\Section\Segments\SegmentItem;

use App\Ds2013\Presenters\Section\Segments\SegmentItem\SpeechPresenter;
use App\DsShared\Helpers\PlayTranslationsHelper;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;
use PHPUnit\Framework\TestCase;

class SpeechPresenterTest extends TestCase
{
    /** @var PlayTranslationsHelper */
    private $mockPlayTranslationsHelper;

    public function setup()
    {
        $this->mockPlayTranslationsHelper = $this->createMock(PlayTranslationsHelper::class);
    }

    /** @dataProvider hasDurationProvider */
    public function testHasDuration(bool $expected, bool $isChapter, ?int $duration)
    {
        $segment = $this->createConfiguredMock(Segment::class, ['getDuration' => $duration]);
        $segmentEvent = $this->createConfiguredMock(
            SegmentEvent::class,
            ['getSegment' => $segment, 'isChapter' => $isChapter]
        );

        $presenter = new SpeechPresenter($this->mockPlayTranslationsHelper, $segmentEvent, []);

        $this->assertEquals($expected, $presenter->hasDuration());
    }

    public function hasDurationProvider()
    {
        return [
            'not chapter and null duration' => [false, false, null],
            'is chapter but null duration' => [false, true, null],
            'is chapter and has non-null duration' => [true, true, 60],
            'not chapter but has non-null duration' => [false, false, 60],
        ];
    }

    /** @dataProvider getTitleProvider */
    public function testGetTitle(string $expected, ?string $title)
    {
        $segment = $this->createConfiguredMock(Segment::class, ['getTitle' => $title]);
        $segmentEvent = $this->createConfiguredMock(SegmentEvent::class, ['getSegment' => $segment]);

        $presenter = new SpeechPresenter($this->mockPlayTranslationsHelper, $segmentEvent, []);

        $this->assertEquals($expected, $presenter->getTitle());
    }

    public function getTitleProvider()
    {
        return [
            'empty title' => ['Untitled', ''],
            'null title' => ['Untitled', null],
            'non-empty title' => ['Segment Title', 'Segment Title'],
        ];
    }
}
