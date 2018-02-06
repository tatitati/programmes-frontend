<?php
declare(strict_types=1);

namespace Tests\App\Ds2013\Presenters\Domain\BroadcastEvent;

use App\Ds2013\Presenters\Domain\BroadcastEvent\BroadcastEventPresenter;
use App\DsShared\Helpers\BroadcastNetworksHelper;
use App\DsShared\Helpers\LocalisedDaysAndMonthsHelper;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGenerator;
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
}
