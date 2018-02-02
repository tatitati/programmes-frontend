<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Presenters\Section\EpisodeSubNav\NavigationItems;

use App\Ds2013\Presenters\Section\EpisodesSubNav\NavigationItems\All;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use PHPUnit\Framework\TestCase;

class AllTest extends TestCase
{
    public function testTranslationString()
    {
        $pid = new Pid('zzzzzzzz');
        $navigationItem = new All($pid, true);
        $this->assertSame('all', $navigationItem->getTranslationString());
    }

    public function testRoute()
    {
        $pid = new Pid('zzzzzzzz');
        $navigationItem = new All($pid, true);
        $this->assertSame('programme_episodes_guide', $navigationItem->getRoute());
    }

    /**
     * @dataProvider selectedDataProvider
     */
    public function testLinkClass(bool $isSelected, string $expectedClasses)
    {
        $pid = new Pid('zzzzzzzz');
        $navigationItem = new All($pid, $isSelected);
        $this->assertSame($expectedClasses, $navigationItem->getLinkClass());
    }

    public function selectedDataProvider(): array
    {
        return [
            'selected' => [true, 'island--squashed br-box-page br-page-link-ontext br-page-linkhover-ontext--hover'],
            'not-selected' => [false, 'island--squashed'],
        ];
    }

    public function testRouteParams()
    {
        $pid = new Pid('zzzzzzzz');
        $navigationItem = new All($pid, true);
        $this->assertSame(['pid' => 'zzzzzzzz'], $navigationItem->getRouteParams());
    }

    public function testShouldShowCount()
    {
        $pid = new Pid('zzzzzzzz');
        $navigationItem = new All($pid, true);
        $this->assertFalse($navigationItem->shouldShowCount());
    }

    public function testShouldShowLink()
    {
        $pid = new Pid('zzzzzzzz');
        $navigationItem = new All($pid, true);
        $this->assertTrue($navigationItem->shouldShowLink());
    }
}
