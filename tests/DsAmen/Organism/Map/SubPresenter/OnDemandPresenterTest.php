<?php
declare(strict_types = 1);

namespace Tests\App\DsAmen\Organism\Map\SubPresenter;

use App\DsAmen\Organism\Map\SubPresenter\OnDemandPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;

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
        new OnDemandPresenter($programme, null, null, null, $options);
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
        $odPresenter = new OnDemandPresenter($programme, $episode, null, null);
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
    public function testNewBadge(bool $isRadio)
    {
        $episode = $this->createEpisode(2);
        $programme = $this->createProgramme($isRadio);
        $odPresenter = new OnDemandPresenter($programme, $episode, null, null);
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
    public function testNewSeriesBadge(bool $isRadio)
    {
        $episode = $this->createEpisode(1);
        $programme = $this->createProgramme($isRadio);
        $odPresenter = new OnDemandPresenter($programme, $episode, null, null);
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
    public function testComingSoonBadge(bool $isRadio)
    {
        $episode = $this->createEpisode();
        $programme = $this->createProgramme($isRadio);
        $odPresenter = new OnDemandPresenter($programme, null, $episode, null);
        $this->assertEquals('coming_soon', $odPresenter->getBadgeTranslationString());
    }

    public function testJustMissed()
    {
        $episode = $this->createEpisode();
        $programme = $this->createProgramme(true);
        $collapsedBroadcast = $this->createMock(CollapsedBroadcast::class);
        $collapsedBroadcast->method('getStartAt')->willReturn(new Chronos('-8 days'));
        $odPresenter = new OnDemandPresenter($programme, null, $episode, $collapsedBroadcast);
        $this->assertTrue($odPresenter->justMissed());
    }

    public function testNotJustMissed()
    {
        $programme = $this->createProgramme(true);
        $odPresenter = new OnDemandPresenter($programme, null, null, null);
        $this->assertFalse($odPresenter->justMissed());

        $collapsedBroadcast = $this->createMock(CollapsedBroadcast::class);
        $collapsedBroadcast->method('getStartAt')->willReturn(Chronos::now());
        $odPresenter = new OnDemandPresenter($programme, null, null, $collapsedBroadcast);
        $this->assertFalse($odPresenter->justMissed());

        $pid = new Pid('bbblsthclwn');
        $programmeItem = $this->createMock(ProgrammeItem::class);
        $programmeItem->method('getPid')->willReturn($pid);
        $collapsedBroadcast = $this->createMock(CollapsedBroadcast::class);
        $collapsedBroadcast->method('getProgrammeItem')->willReturn($programmeItem);
        $episode = $this->createEpisode();
        $episode->method('getPid')->willReturn($pid);
        $odPresenter = new OnDemandPresenter($programme, $episode, null, $collapsedBroadcast);
        $this->assertFalse($odPresenter->justMissed());

        $pid = new Pid('ntbbblsthclwn');
        $programmeItem = $this->createMock(ProgrammeItem::class);
        $programmeItem->method('getPid')->willReturn($pid);
        $collapsedBroadcast = $this->createMock(CollapsedBroadcast::class);
        $collapsedBroadcast->method('getProgrammeItem')->willReturn($programmeItem);
        $collapsedBroadcast->method('getStartAt')->willReturn(Chronos::now());
        $odPresenter = new OnDemandPresenter($programme, $episode, null, $collapsedBroadcast);
        $this->assertFalse($odPresenter->justMissed());
    }

    private function createEpisode(int $position = 1): Episode
    {
        $episode = $this->createMock(Episode::class);
        $episode->method('getParent')
            ->willReturn($this->createMock(Series::class));
        $episode->method('getFirstBroadcastDate')
            ->willReturn(new Chronos('-1 day'));
        $episode->method('getPosition')
            ->willReturn($position);
        $episode->method('getStreamableFrom')
            ->willReturn(new Chronos('+1 day'));
        return $episode;
    }

    private function createProgramme(bool $isRadio = true): ProgrammeContainer
    {
        $programme = $this->createMock(ProgrammeContainer::class);
        $programme->method('isRadio')
            ->willReturn($isRadio);
        $programme->method('isTv')
            ->willReturn(!$isRadio);
        return $programme;
    }
}
