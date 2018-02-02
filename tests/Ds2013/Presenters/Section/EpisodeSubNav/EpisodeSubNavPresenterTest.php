<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Presenters\Section\EpisodeSubNav;

use App\Ds2013\Presenters\Section\EpisodesSubNav\EpisodesSubNavPresenter;
use App\Ds2013\Presenters\Section\EpisodesSubNav\NavigationItems\All;
use App\Ds2013\Presenters\Section\EpisodesSubNav\NavigationItems\AvailableNow;
use App\Ds2013\Presenters\Section\EpisodesSubNav\NavigationItems\ByDate;
use App\Ds2013\Presenters\Section\EpisodesSubNav\NavigationItems\NextOn;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use PHPUnit\Framework\TestCase;

class EpisodeSubNavPresenterTest extends TestCase
{
    public function testOnlyAll()
    {
        $pid = new Pid('zzzzzzzz');
        $presenter = new EpisodesSubNavPresenter('a_route', false, false, 0, $pid, 0);

        $items = $presenter->getItems();
        $this->assertCount(1, $items);
        $this->assertInstanceOf(All::class, $items[0]);
    }

    /**
     * @dataProvider availableNowProvider
     */
    public function testOnlyAllAndAvailableNow(bool $isDomestic, int $episodeCount)
    {
        $pid = new Pid('zzzzzzzz');
        $presenter = new EpisodesSubNavPresenter('a_route', $isDomestic, false, $episodeCount, $pid, 0);

        $items = $presenter->getItems();
        $this->assertCount(2, $items);
        $this->assertInstanceOf(All::class, $items[0]);
        $this->assertInstanceOf(AvailableNow::class, $items[1]);
    }

    public function availableNowProvider(): array
    {
        return [
            'domestic' => [true, 0],
            'episodes-available' => [false, 1],
        ];
    }

    public function testAllButAvailableNow()
    {
        $pid = new Pid('zzzzzzzz');
        $presenter = new EpisodesSubNavPresenter('a_route', false, true, 0, $pid, 0);

        $items = $presenter->getItems();
        $this->assertCount(3, $items);
        $this->assertInstanceOf(All::class, $items[0]);
        $this->assertInstanceOf(ByDate::class, $items[1]);
        $this->assertInstanceOf(NextOn::class, $items[2]);
    }

    /**
     * @dataProvider availableNowProvider
     */
    public function testAllNavigationItems(bool $isDomestic, int $episodeCount)
    {
        $pid = new Pid('zzzzzzzz');
        $presenter = new EpisodesSubNavPresenter('a_route', $isDomestic, true, $episodeCount, $pid, 1);

        $items = $presenter->getItems();
        $this->assertCount(4, $items);
        $this->assertInstanceOf(All::class, $items[0]);
        $this->assertInstanceOf(ByDate::class, $items[1]);
        $this->assertInstanceOf(AvailableNow::class, $items[2]);
        $this->assertInstanceOf(NextOn::class, $items[3]);
    }
}
