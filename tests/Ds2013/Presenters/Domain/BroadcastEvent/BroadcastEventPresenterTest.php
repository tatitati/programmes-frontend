<?php
declare(strict_types=1);

namespace Tests\App\Ds2013\Presenters\Domain\BroadcastEvent;

use App\Builders\CollapsedBroadcastBuilder;
use App\Builders\EpisodeBuilder;
use App\Builders\ServiceBuilder;
use App\Ds2013\Presenters\Domain\BroadcastEvent\BroadcastEventPresenter;
use App\DsShared\Helpers\BroadcastNetworksHelper;
use App\DsShared\Helpers\LiveBroadcastHelper;
use App\DsShared\Helpers\LocalisedDaysAndMonthsHelper;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\ValueObject\Sid;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollectionBuilder;

class BroadcastEventPresenterTest extends TestCase
{
    private $mockCollapsedBroadcast;
    private $router;
    private $mockBroadcastNetworksHelper;
    private $mockLocalisedDaysAndMonthsHelper;

    public function setUp()
    {
        $routeCollectionBuilder = new RouteCollectionBuilder();
        $routeCollectionBuilder->add('/{networkUrlKey}', '', 'network');

        $this->router = new UrlGenerator(
            $routeCollectionBuilder->build(),
            new RequestContext()
        );

        $this->mockCollapsedBroadcast = $this->createMock(CollapsedBroadcast::class);
        $this->mockBroadcastNetworksHelper = $this->createMock(BroadcastNetworksHelper::class);
        $this->mockLocalisedDaysAndMonthsHelper = $this->createMock(LocalisedDaysAndMonthsHelper::class);
    }

    /** @dataProvider getNetworkUrlProvider */
    public function testGetNetworkUrl(?Network $network, string $expected)
    {
        $presenter = new BroadcastEventPresenter(
            $this->mockCollapsedBroadcast,
            $this->mockBroadcastNetworksHelper,
            $this->mockLocalisedDaysAndMonthsHelper,
            $this->createMock(LiveBroadcastHelper::class),
            $this->router
        );

        $actual = $presenter->getNetworkUrl($network);
        $this->assertSame($expected, $actual);
    }

    public function getNetworkUrlProvider(): array
    {
        $network = $this->createConfiguredMock(Network::class, ['getUrlKey' => 'radio4']);

        return [
            'network returns absolute url'  => [$network, 'http://localhost/radio4'],
            'null returns empty url' => [null, ''],
        ];
    }

    public function servicesTypeProvider()
    {
        return [
            [$this->getListServicesDoingLiveBroadcasts(), true],
            [$this->getListServicesNotDoingLiveBroadcasts(), false],
        ];
    }

    /**
     * [ watch from the start ]. Know when a watchable broadcast should own a simulcast url
     */
    public function testGivenWatchableAndEpisodeIsTv()
    {
        $routerMock = $this->createMock(UrlGeneratorInterface::class);
        $routerMock
            ->expects($this->once())
            ->method('generate')
            ->with('iplayer_live', ['sid' => 'bbcone', 'area' => 'scotland', 'rewindTo' => 'current'])
            ->willReturn('any string');

        $cBroadcast = $this->buildLiveBroadcastWith($this->getListServicesDoingLiveBroadcasts(), 'tv');

        $this->buildPresenterForBroadcast($cBroadcast, $routerMock)->getRewindUrl();
    }

    public function testGivenWatchableAndEpisodeIsRadio()
    {
        $routerMock = $this->createMock(UrlGeneratorInterface::class);
        $routerMock
            ->expects($this->never())
            ->method('generate');

        $cBroadcast = $this->buildLiveBroadcastWith($this->getListServicesDoingLiveBroadcasts(), 'radio');

        $this->buildPresenterForBroadcast($cBroadcast, $routerMock)->getRewindUrl();
    }

    /**
     * Build watchable broadcast to exercise simulcast feature.
     *
     * @param Service[] $services
     * @param string $episodeType
     */
    private function buildLiveBroadcastWith(array $services, string $episodeType = 'any') :CollapsedBroadcast
    {
        $episode = null;
        if ($episodeType == 'tv') {
            $episode = EpisodeBuilder::anyTVEpisode()->build();
        } elseif ($episodeType == 'radio') {
            $episode = EpisodeBuilder::anyRadioEpisode()->build();
        } else {
            $episode = EpisodeBuilder::any()->build();
        }

        return CollapsedBroadcastBuilder::anyLive()->with([
            'programmeItem' => $episode,
            'isBlanked' => false,
            'services' => $services,
        ])->build();
    }

    private function buildPresenterForBroadcast(CollapsedBroadcast $cBroadcast, $routerMock) :BroadcastEventPresenter
    {
        return new BroadcastEventPresenter(
            $cBroadcast,
            $this->mockBroadcastNetworksHelper,
            $this->mockLocalisedDaysAndMonthsHelper,
            new LiveBroadcastHelper($routerMock),
            $routerMock
        );
    }

    /**
     * @return Service[]
     */
    private function getListServicesDoingLiveBroadcasts(): array
    {
        return [
            ServiceBuilder::any()->with(['sid' => new Sid('bbc_two_wales_digital')])->build(),
            ServiceBuilder::any()->with(['sid' => new Sid('bbc_one_scotland')])->build(),
        ];
    }

    /**
     * @return Service[]
     */
    private function getListServicesNotDoingLiveBroadcasts(): array
    {
        return [
            ServiceBuilder::any()->with(['sid' => new Sid('bbc_network_without_live_broadcast1')])->build(),
            ServiceBuilder::any()->with(['sid' => new Sid('bbc_network_without_live_broadcast2')])->build(),
        ];
    }
}
