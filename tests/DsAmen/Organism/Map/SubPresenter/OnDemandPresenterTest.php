<?php
declare(strict_types = 1);

namespace Tests\App\DsAmen\Organism\Map\SubPresenter;

use App\DsAmen\Organism\Map\SubPresenter\OnDemandPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class OnDemandPresenterTest extends TestCase
{
    /**
     * @return bool[][]
     */
    public function trueFalseDataProvider(): array
    {
        return [
            'true' => [true],
            'false' => [false],
        ];
    }

    /**
     * @dataProvider invalidOptionProvider
     * @param mixed[][] $options
     * @param string $expectedExceptionMessage
     */
    public function testInvalidOptions(array $options, string $expectedExceptionMessage)
    {
        $programme = $this->createProgramme();
        $this->expectExceptionMessage($expectedExceptionMessage);
        new OnDemandPresenter($programme, null, null, $options);
    }

    public function invalidOptionProvider(): array
    {
        return [
            'invalid-show_mini_map' => [['show_mini_map' => 'bar', 'show_synopsis' => false], 'show_mini_map option must be a boolean'],
            'invalid-full_width' => [['show_mini_map' => true, 'full_width' => 'baz'], 'full_width option must be a boolean'],
        ];
    }

    /**
     * @dataProvider trueFalseDataProvider
     * @param bool $isRadio
     */
    public function testDataLinktrackString(bool $isRadio)
    {
        $episode = $this->createEpisode();

        $programme = $this->createProgramme($isRadio);
        $odPresenter = new OnDemandPresenter($programme, $episode, null);
        if ($isRadio) {
            $this->assertEquals('map_ondemand_all', $odPresenter->getAllLinkLocation());
        } else {
            $this->assertEquals('map_iplayer_all', $odPresenter->getAllLinkLocation());
        }
    }

    /**
     * @dataProvider trueFalseDataProvider
     * @param bool $isRadio
     */
    public function testNoStreamableOrUpcomingEpisodes(bool $isRadio)
    {
        $programme = $this->createProgramme($isRadio);
        $odPresenter = new OnDemandPresenter($programme, null, null);
        $this->assertNull($odPresenter->getStreamableEpisode());
        $this->assertNull($odPresenter->getUpcomingEpisode());
        $this->assertFalse($odPresenter->shouldShowImage()); // Not that these are likely ever called
        $this->assertFalse($odPresenter->lastOnNotAvailableYet()); // Not that these are likely ever called
        $this->expectExceptionMessage('Streamable or LastOn must be set in order to call getBadgeTranslationString');
        $this->assertEmpty($odPresenter->getBadgeTranslationString());
    }

    /**
     * @dataProvider trueFalseDataProvider
     * @param bool $isRadio
     */
    public function testOldStreamableEpisodeNoUpcomingEpisode(bool $isRadio)
    {
        $episode = $this->createEpisode(2, '+1 day', '-8 days');
        $programme = $this->createProgramme($isRadio);
        $odPresenter = new OnDemandPresenter($programme, $episode, null);
        $this->assertSame($episode, $odPresenter->getStreamableEpisode());
        $this->assertNull($odPresenter->getUpcomingEpisode());
        $this->assertFalse($odPresenter->lastOnNotAvailableYet());
        $this->assertEmpty($odPresenter->getBadgeTranslationString());
        $this->assertTrue($odPresenter->shouldShowImage());
    }

    /**
     * @dataProvider trueFalseDataProvider
     * @param bool $isRadio
     */
    public function testNewStreamableEpisodeNoUpcomingEpisode(bool $isRadio)
    {
        $episode = $this->createEpisode(2, '+1 day', '-6 days');
        $programme = $this->createProgramme($isRadio);
        $odPresenter = new OnDemandPresenter($programme, $episode, null);
        $this->assertSame($episode, $odPresenter->getStreamableEpisode());
        $this->assertNull($odPresenter->getUpcomingEpisode());
        $this->assertFalse($odPresenter->lastOnNotAvailableYet());
        $this->assertTrue($odPresenter->shouldShowImage());
        if ($isRadio) {
            $this->assertEmpty($odPresenter->getBadgeTranslationString());
        } else {
            $this->assertEquals('new', $odPresenter->getBadgeTranslationString());
        }
    }

    /**
     * @dataProvider trueFalseDataProvider
     * @param bool $isRadio
     */
    public function testNewStreamableSeriesEpisodeNoUpcomingEpisode(bool $isRadio)
    {
        $episode = $this->createEpisode(1, '+1 day', '-6 days');
        $programme = $this->createProgramme($isRadio);
        $odPresenter = new OnDemandPresenter($programme, $episode, null);
        $this->assertSame($episode, $odPresenter->getStreamableEpisode());
        $this->assertNull($odPresenter->getUpcomingEpisode());
        $this->assertTrue($odPresenter->shouldShowImage());
        if ($isRadio) {
            $this->assertEmpty($odPresenter->getBadgeTranslationString());
        } else {
            $this->assertEquals('new_series', $odPresenter->getBadgeTranslationString());
        }
    }

    /**
     * @dataProvider trueFalseDataProvider
     * @param bool $isRadio
     */
    public function testStreamableEpisodeAndUpcomingEpisode(bool $isRadio)
    {
        $episode = $this->createEpisode(2, '-8 days', '-8 days', true);
        $programme = $this->createProgramme($isRadio);
        $collapsedBroadcast = $this->createMock(CollapsedBroadcast::class);
        $collapsedBroadcast->method('getStartAt')
            ->willReturn(new Chronos('-8 days'));
        $collapsedBroadcast->method('getProgrammeItem')
            ->willReturn($episode);
        $odPresenter = new OnDemandPresenter($programme, $episode, $collapsedBroadcast);
        $this->assertSame($episode, $odPresenter->getStreamableEpisode());
        $this->assertSame($episode, $odPresenter->getUpcomingEpisode());
        $this->assertFalse($odPresenter->lastOnNotAvailableYet());
        $this->assertEmpty($odPresenter->getBadgeTranslationString());
        $this->assertTrue($odPresenter->shouldShowImage());
    }

    /**
     * @dataProvider trueFalseDataProvider
     * @param bool $isRadio
     */
    public function testStreamableEpisodeAndUpcomingSoonEpisode(bool $isRadio)
    {
        $episode = $this->createEpisode(2, '-6 days', '-6 days', false);
        $programme = $this->createProgramme($isRadio);
        $collapsedBroadcast = $this->createMock(CollapsedBroadcast::class);
        $collapsedBroadcast->method('getStartAt')
            ->willReturn(new Chronos('-6 days'));
        $collapsedBroadcast->method('getProgrammeItem')
            ->willReturn($episode);
        $odPresenter = new OnDemandPresenter($programme, $episode, $collapsedBroadcast);
        $this->assertSame($episode, $odPresenter->getStreamableEpisode());
        $this->assertSame($episode, $odPresenter->getUpcomingEpisode());
        $this->assertTrue($odPresenter->lastOnNotAvailableYet());
        $this->assertEquals('coming_soon', $odPresenter->getBadgeTranslationString());
        $this->assertTrue($odPresenter->shouldShowImage());
    }

    /**
     * @dataProvider trueFalseDataProvider
     * @param bool $isRadio
     */
    public function testStreamableEpisodeAndUpcomingEpisodeIsTooOld(bool $isRadio)
    {
        $episode = $this->createEpisode(2, '-8 days', '-8 days', false);
        $programme = $this->createProgramme($isRadio);
        $collapsedBroadcast = $this->createMock(CollapsedBroadcast::class);
        $collapsedBroadcast->method('getStartAt')
            ->willReturn(new Chronos('-8 days'));
        $collapsedBroadcast->method('getProgrammeItem')
            ->willReturn($episode);
        $odPresenter = new OnDemandPresenter($programme, $episode, $collapsedBroadcast);
        $this->assertSame($episode, $odPresenter->getStreamableEpisode());
        $this->assertSame($episode, $odPresenter->getUpcomingEpisode());
        $this->assertFalse($odPresenter->lastOnNotAvailableYet());
        $this->assertEmpty($odPresenter->getBadgeTranslationString());
        $this->assertTrue($odPresenter->shouldShowImage());
    }

    /**
     * @dataProvider trueFalseDataProvider
     * @param bool $isRadio
     */
    public function testComingSoonBadge(bool $isRadio)
    {
        $episode = $this->createEpisode(1, '-1 day');
        $programme = $this->createProgramme($isRadio);
        $collapsedBroadcast = $this->createMock(CollapsedBroadcast::class);
        $collapsedBroadcast->method('getStartAt')
            ->willReturn(new Chronos('-6 days'));
        $collapsedBroadcast->method('getProgrammeItem')
            ->willReturn($episode);
        $odPresenter = new OnDemandPresenter($programme, null, $collapsedBroadcast);
        $this->assertEquals('coming_soon', $odPresenter->getBadgeTranslationString());
    }

    /**
     * @dataProvider trueFalseDataProvider
     * @param bool $isRadio
     */
    public function testBadgeIsNotShowWhenParentIsTleo(bool $isRadio)
    {
        $parent = $this->createMock(Brand::class);
        $parent->method('isTleo')
            ->willReturn(true);
        $episode = $this->createMock(Episode::class);
        $episode->method('getParent')
            ->willReturn($parent);
        $episode->method('getFirstBroadcastDate')
            ->willReturn(new Chronos());
        $episode->method('getPosition')
            ->willReturn(1);
        $programme = $this->createProgramme($isRadio);
        $odPresenter = new OnDemandPresenter($programme, $episode, null);
        $this->assertEmpty($odPresenter->getBadgeTranslationString());
    }

    public function testLastOnNotAvailableYet()
    {
        $episode = $this->createEpisode();
        $programme = $this->createProgramme();
        $collapsedBroadcast = $this->createMock(CollapsedBroadcast::class);
        $collapsedBroadcast->method('getStartAt')->willReturn(new Chronos('-6 days'));
        $collapsedBroadcast->method('getProgrammeItem')->willReturn($episode);
        $odPresenter = new OnDemandPresenter($programme, null, $collapsedBroadcast);
        $this->assertTrue($odPresenter->lastOnNotAvailableYet());
    }

    /**
     * @dataProvider trueFalseDataProvider
     * @param bool $isStreamable
     */
    public function testLastOnNotAvailableYetWhenNoStreamableFromDate(bool $isStreamable)
    {
        $episode = $this->createEpisode(1, null, '-1 day', $isStreamable);
        $programme = $this->createProgramme();
        $collapsedBroadcast = $this->createMock(CollapsedBroadcast::class);
        $collapsedBroadcast->method('getProgrammeItem')->willReturn($episode);
        $odPresenter = new OnDemandPresenter($programme, null, $collapsedBroadcast);
        $this->assertFalse($odPresenter->lastOnNotAvailableYet());
    }

    public function testLastOnNotAvailableYetWhenStreamable()
    {
        $episode = $this->createEpisode(1, '+1 day', '-1 day', true);
        $programme = $this->createProgramme();
        $collapsedBroadcast = $this->createMock(CollapsedBroadcast::class);
        $collapsedBroadcast->method('getProgrammeItem')->willReturn($episode);
        $odPresenter = new OnDemandPresenter($programme, null, $collapsedBroadcast);
        $this->assertFalse($odPresenter->lastOnNotAvailableYet());
    }

    public function testLastOnNotAvailableYetWhenOver7DaysOld()
    {
        $episode = $this->createEpisode();
        $programme = $this->createProgramme();
        $collapsedBroadcast = $this->createMock(CollapsedBroadcast::class);
        $collapsedBroadcast->method('getStartAt')->willReturn(new Chronos('-8 days'));
        $collapsedBroadcast->method('getProgrammeItem')->willReturn($episode);
        $odPresenter = new OnDemandPresenter($programme, null, $collapsedBroadcast);
        $this->assertFalse($odPresenter->lastOnNotAvailableYet());
    }

    public function testShowImageWhenShowingMiniMap()
    {
        $episodeImage = $this->createMock(Image::class);
        $episodeImage->method('getPid')
            ->willReturn(new Pid('v0t3m1k3'));
        $programmeImage = $this->createMock(Image::class);
        $programmeImage->method('getPid')
            ->willReturn(new Pid('s0m3t1ng'));
        $episode = $this->createEpisode(1, '-1 day');
        $episode->method('getImage')
            ->willReturn($episodeImage);
        $programme = $this->createProgramme(true);
        $programme->method('getImage')
            ->willReturn($programmeImage);
        $collapsedBroadcast = $this->createMock(CollapsedBroadcast::class);
        $collapsedBroadcast->method('getStartAt')->willReturn(new Chronos('-6 days'));
        $collapsedBroadcast->method('getProgrammeItem')
            ->willReturn($episode);
        $odPresenter = new OnDemandPresenter($programme, $episode, $collapsedBroadcast, ['show_mini_map' => true]);
        $this->assertFalse($odPresenter->shouldShowImage());
    }

    public function testShowImageWhenPidsMatch()
    {
        $episodeImage = $this->createMock(Image::class);
        $episodeImage->method('getPid')
            ->willReturn(new Pid('v0t3m1k3'));
        $programmeImage = $this->createMock(Image::class);
        $programmeImage->method('getPid')
            ->willReturn(new Pid('v0t3m1k3'));
        $episode = $this->createEpisode(1, '-1 day');
        $episode->method('getImage')
            ->willReturn($episodeImage);
        $programme = $this->createProgramme(true);
        $programme->method('getImage')
            ->willReturn($programmeImage);
        $collapsedBroadcast = $this->createMock(CollapsedBroadcast::class);
        $collapsedBroadcast->method('getStartAt')->willReturn(new Chronos('-6 days'));
        $collapsedBroadcast->method('getProgrammeItem')
            ->willReturn($episode);
        $odPresenter = new OnDemandPresenter($programme, $episode, $collapsedBroadcast);
        $this->assertFalse($odPresenter->shouldShowImage());
    }

    /**
     * @param int $position
     * @param null|string $streamableFrom
     * @return Episode|PHPUnit_Framework_MockObject_MockObject
     */
    private function createEpisode(int $position = 1, ?string $streamableFrom = '+1 day', string $firstBroadcast = '-1 day', bool $isStreamable = false): Episode
    {
        $episode = $this->createMock(Episode::class);
        $episode->method('getParent')
            ->willReturn($this->createMock(Series::class));
        $episode->method('getFirstBroadcastDate')
            ->willReturn(new Chronos($firstBroadcast));
        $episode->method('getPosition')
            ->willReturn($position);
        if ($streamableFrom === null) {
            $episode->method('getStreamableFrom')
                ->willReturn(null);
        } else {
            $episode->method('getStreamableFrom')
                ->willReturn(new Chronos($streamableFrom));
        }
        $episode->method('isStreamable')
            ->willReturn($isStreamable);
        return $episode;
    }

    /**
     * @param bool $isRadio
     * @return ProgrammeContainer|PHPUnit_Framework_MockObject_MockObject
     */
    private function createProgramme(bool $isRadio = true): ProgrammeContainer
    {
        $programmeImage = $this->createMock(Image::class);
        $programmeImage->method('getPid')
            ->willReturn(new Pid('v0t3m1k3'));

        $programme = $this->createMock(ProgrammeContainer::class);
        $programme->method('isRadio')
            ->willReturn($isRadio);
        $programme->method('isTv')
            ->willReturn(!$isRadio);
        $programme->method('getImage')
            ->willReturn($programmeImage);
        return $programme;
    }
}
