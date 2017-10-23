<?php
declare(strict_types = 1);

namespace Tests\App\DsAmen\Organism\Map;

use App\DsAmen\Organism\Map\MapPresenter;
use App\DsAmen\Organism\Map\SubPresenter\ComingSoonPresenter;
use App\DsAmen\Organism\Map\SubPresenter\LastOnPresenter;
use App\DsAmen\Organism\Map\SubPresenter\OnDemandPresenter;
use App\DsAmen\Organism\Map\SubPresenter\TxPresenter;
use App\DsAmen\Presenter;
use App\DsShared\Helpers\HelperFactory;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\Promotion;
use BBC\ProgrammesPagesService\Domain\ValueObject\Nid;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MapPresenterTest extends TestCase
{
    public function testMapShouldBeShown()
    {
        $programmeContainer = $this->createProgrammeWithEpisodes();
        $presenter = new MapPresenter(
            new Request(),
            $this->createMock(HelperFactory::class),
            $this->createMock(TranslateProvider::class),
            $this->createMock(UrlGeneratorInterface::class),
            $programmeContainer,
            null,
            null,
            null,
            null,
            null,
            0,
            0,
            false
        );
        $this->assertTrue($presenter->showMap());

        $programmeContainer = $this->createMock(ProgrammeContainer::class);
        $programmeContainer->method('getAggregatedEpisodesCount')->willReturn(0);
        $programmeContainer->expects($this->atLeastOnce())->method('getOption')
            ->will($this->returnValueMap([
                ['comingsoon_textonly', 'Coming soon text'],
            ]));
        $presenter = $this->createMapPresenter($programmeContainer);
        $this->assertTrue($presenter->showMap());
    }

    public function testMapShouldNotBeShown()
    {
        $programmeContainer = $this->createMock(ProgrammeContainer::class);
        $presenter = $this->createMapPresenter($programmeContainer);
        $this->assertFalse($presenter->showMap());
    }

    public function testComingSoonTakeoverColumns()
    {
        $programmeContainer = $this->createMock(ProgrammeContainer::class);
        $programmeContainer->method('getAggregatedEpisodesCount')->willReturn(0);
        $programmeContainer->expects($this->atLeastOnce())->method('getOption')
            ->will($this->returnValueMap([
                ['comingsoon_textonly', 'Coming soon text'],
            ]));
        $presenter = $this->createMapPresenter($programmeContainer);
        $this->assertColumns($presenter, [OnDemandPresenter::class, ComingSoonPresenter::class]);
    }

    public function testWorldNewsColumns()
    {
        $network = $this->createMock(Network::class);
        $network->method('getNid')->willReturn(new Nid('bbc_world_news'));
        $programmeContainer = $this->createProgrammeWithEpisodes();
        $programmeContainer->method('getNetwork')->willReturn($network);
        $presenter = $this->createMapPresenter($programmeContainer);
        $this->assertColumns($presenter, [LastOnPresenter::class, TxPresenter::class]);
    }

    public function testTxColumns()
    {
        $cb = $this->createMock(CollapsedBroadcast::class);
        $programmeContainer = $this->createProgrammeWithEpisodes();
        $presenter = $this->createMapPresenter($programmeContainer, $cb);
        $this->assertColumns($presenter, [OnDemandPresenter::class, TxPresenter::class]);
    }

    public function testDefaultColumns()
    {
        $programmeContainer = $this->createProgrammeWithEpisodes();
        $presenter = $this->createMapPresenter($programmeContainer);
        $this->assertColumns($presenter, [OnDemandPresenter::class]);
    }

    public function testShowPromoPriority()
    {
        $promo = $this->createMock(Promotion::class);
        $programmeContainer = $this->createMock(ProgrammeContainer::class);
        $programmeContainer->method('getAggregatedEpisodesCount')->willReturn(1);
        $programmeContainer->method('isTlec')->willReturn(true);
        $programmeContainer->expects($this->atLeastOnce())->method('getOption')
            ->will($this->returnValueMap([
                ['brand_layout', 'promo'],
                ['brand_2016_layout_use_minimap', false],
            ]));
        $presenter = $this->createMapPresenter($programmeContainer, null, $promo);
        $this->assertTrue($presenter->isPromoPriority());
    }

    /**
     * Asserts the correct number of columns exists in the correct order
     *
     * @param MapPresenter $presenter
     * @param string[] $columns Full class names of expected columns
     */
    private function assertColumns(MapPresenter $presenter, array $columns)
    {
        $presenterColumns = $presenter->getRightColumns();
        $this->assertContainsOnlyInstancesOf(Presenter::class, $presenterColumns);
        $this->assertCount(count($columns), $presenterColumns);
        foreach ($columns as $key => $column) {
            $this->assertInstanceOf($column, $presenterColumns[$key]);
        }
    }

    private function createMapPresenter($programmeContainer, ?CollapsedBroadcast $upcomingBroadcasts = null, ?Promotion $firstPromo = null): MapPresenter
    {
        return new MapPresenter(
            new Request(),
            $this->createMock(HelperFactory::class),
            $this->createMock(TranslateProvider::class),
            $this->createMock(UrlGeneratorInterface::class),
            $programmeContainer,
            $upcomingBroadcasts,
            null,
            $firstPromo,
            null,
            null,
            0,
            0,
            false
        );
    }

    /**
     * @return ProgrammeContainer|PHPUnit_Framework_MockObject_MockObject
     */
    private function createProgrammeWithEpisodes(): ProgrammeContainer
    {
        $programmeContainer = $this->createMock(ProgrammeContainer::class);
        $programmeContainer->method('getAggregatedEpisodesCount')->willReturn(1);

        return $programmeContainer;
    }
}
