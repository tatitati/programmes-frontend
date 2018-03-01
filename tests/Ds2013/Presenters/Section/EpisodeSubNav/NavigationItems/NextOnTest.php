<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Presenters\Section\EpisodeSubNav\NavigationItems;

use App\Ds2013\Presenters\Section\EpisodesSubNav\NavigationItems\NextOn;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use PHPUnit\Framework\TestCase;

class NextOnTest extends TestCase
{
    public function testTranslationString()
    {
        $pid = new Pid('zzzzzzzz');
        $navigationItem = new NextOn($pid, true, 0);
        $this->assertSame('next_on', $navigationItem->getTranslationString());
    }

    public function testRoute()
    {
        $pid = new Pid('zzzzzzzz');
        $navigationItem = new NextOn($pid, true, 0);
        $this->assertSame('programme_upcoming_broadcasts', $navigationItem->getRoute());
    }

    /**
     * @dataProvider linkClassDataProvider
     */
    public function testLinkClass(bool $isSelected, int $broadcastCount, string $expectedClasses)
    {
        $pid = new Pid('zzzzzzzz');
        $navigationItem = new NextOn($pid, $isSelected, $broadcastCount);
        $this->assertSame($expectedClasses, $navigationItem->getLinkClass());
    }

    public function linkClassDataProvider(): array
    {
        return [
            'selected-link' => [true, 1, 'island--squashed br-box-page br-page-link-ontext br-page-linkhover-ontext--hover'],
            'not-selected-link' => [false, 1, 'island--squashed'],
            'selected-no-link' => [true, 0, 'island--squashed br-box-page br-page-text-ontext'],
            'not-selected-no-link' => [false, 0, 'island--squashed text--subtle'],
        ];
    }

    public function testRouteParams()
    {
        $pid = new Pid('zzzzzzzz');
        $navigationItem = new NextOn($pid, true, 0);
        $this->assertSame(['pid' => 'zzzzzzzz'], $navigationItem->getRouteParams());
    }

    public function testShouldShowCount()
    {
        $pid = new Pid('zzzzzzzz');
        $navigationItem = new NextOn($pid, true, 0);
        $this->assertTrue($navigationItem->shouldShowCount());
    }

    /**
     * @dataProvider showLinkDataProvider
     */
    public function testShouldShowLink(int $broadcastCount, bool $expectedResult)
    {
        $pid = new Pid('zzzzzzzz');
        $navigationItem = new NextOn($pid, true, $broadcastCount);
        $this->assertSame($expectedResult, $navigationItem->shouldShowLink());
    }

    public function showLinkDataProvider(): array
    {
        return [
            'show-link' => [true, 1],
            'dont-show-link' => [false, 0],
        ];
    }

    public function testGetCount()
    {
        $pid = new Pid('zzzzzzzz');
        $navigationItem = new NextOn($pid, true, 42);
        $this->assertSame(42, $navigationItem->getCount());
    }
}
