<?php
declare(strict_types = 1);

namespace Tests\App\Controller\FindByPid;

use App\Controller\FindByPid\TlecController;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\BaseWebTestCase;

class TlecControllerTest extends BaseWebTestCase
{
    public function tearDown()
    {
        ApplicationTime::blank();
    }

    public function testIsVotePriority()
    {
        $controller = $this->createMock(TlecController::class);

        $programmeContainer = $this->createMock(ProgrammeContainer::class);

        $programmeContainer->expects($this->atLeastOnce())->method('getOption')
            ->will($this->returnValueMap([
                ['brand_layout', 'vote'],
                ['telescope_block', 'anythingthatisntnull'],
            ]));

        $this->assertTrue($this->invokeMethod($controller, 'isVotePriority', [$programmeContainer]));
    }

    /** @dataProvider listOfNetworksIsFetchedOnlyForWorldNewsLastOnProvider */
    public function testListOfNetworksIsFetchedOnlyForWorldNewsLastOn(
        bool $isWorldNews,
        int $fullListCalls,
        int $regularCalls
    ) {
        $programme = $this->createConfiguredMock(
            ProgrammeContainer::class,
            [
                'getNetwork' => $this->createConfiguredMock(
                    Network::class,
                    ['isWorldNews' => $isWorldNews]
                ),
            ]
        );

        $collapsedBroadcastService = $this->createMock(CollapsedBroadcastsService::class);
        $collapsedBroadcastService
            ->expects($this->exactly($fullListCalls))
            ->method('findPastByProgrammeWithFullServicesOfNetworksList');
        $collapsedBroadcastService
            ->expects($this->exactly($regularCalls))
            ->method('findPastByProgramme');

        $controller = $this->createMock(TlecController::class);
        $this->invokeMethod($controller, 'getLastOn', [$programme, $collapsedBroadcastService]);
    }

    public function listOfNetworksIsFetchedOnlyForWorldNewsLastOnProvider(): array
    {
        return [
            'World News' => [true, 1, 0],
            'Non World News' => [false, 0, 1],
        ];
    }

    /**
     * @dataProvider showMiniMapDataProvider
     */
    public function testShowMiniMap(Request $request, ProgrammeContainer $programmeContainer, bool $isPromoPriority, bool $hasLxPromo)
    {
        $controller = $this->createMock(TlecController::class);

        $showMiniMap = $this->invokeMethod(
            $controller,
            'showMiniMap',
            [
                $request,
                $programmeContainer,
                $isPromoPriority,
                $hasLxPromo,
            ]
        );
        $this->assertTrue($showMiniMap);
    }

    public function showMiniMapDataProvider(): array
    {
        $cases = [];
        $programmeContainer = $this->createMock(ProgrammeContainer::class);
        $cases['is-vote-priority'] = [new Request(), clone $programmeContainer, true, false];
        $cases['has-lx-promo'] = [new Request(), clone $programmeContainer, true, true];
        $cases['forced-by-url'] = [new Request(['__2016minimap' => 1]), clone $programmeContainer, false];

        $programmeContainer->expects($this->once())
            ->method('getOption')
            ->with('brand_2016_layout_use_minimap')
            ->willReturn('true');

        $cases['forced-by-url'] = [new Request(), $programmeContainer, false, false];

        return $cases;
    }

    public function testTlecIstatsLabels()
    {
        $this->loadFixtures(["FindByPid\Tlec\TlecClipsFixture"]);

        $client = static::createClient();

        for ($i = 1; $i <= 4; $i++) {
            $url = '/programmes/prstdbrnd' . $i;
            $crawler = $client->request('GET', $url);
            $labels = $this->extractIstatsLabels($crawler);

            $this->assertSame('programmes', $labels['app_name']);
            $this->assertSame('programmes', $labels['prod_name']);
            $this->assertTrue(is_numeric($labels['app_version']));
            $this->assertSame('programmes_container', $labels['progs_page_type']);
            $this->assertTrue(!empty($labels['programme_title']));
            $this->assertTrue(!empty($labels['brand_title']));
            $this->assertTrue(isset($labels['pips_genre_group_ids']));
            $this->assertSame('prstdbrnd' . $i, $labels['brand_id']);
            $this->assertSame('2', $labels['rec_v']);
            $this->assertSame('programmes', $labels['rec_app_id']);
            $this->assertSame('null_null_2', $labels['rec_p']);
            $this->assertSame('brand', $labels['container_is']);
            $this->assertSame('true', $labels['is_tleo']);
            $this->assertTrue(in_array($labels['availability'], ['true', 'false']));
            $this->assertTrue(in_array($labels['upcoming'], ['true', 'false']));
            $this->assertTrue(in_array($labels['live_episode'], ['true', 'false']));
            $this->assertTrue(in_array($labels['past_broadcast'], ['true', 'false']));
            $this->assertTrue(in_array($labels['just_missed'], ['true', 'false']));
        }
    }

    public function testSetInternationalStatusAndTimezoneFromContext()
    {
        // check default timezone is Europe/London
        $this->assertSame('Europe/London', ApplicationTime::getLocalTimeZone()->getName());

        $network = $this->createMock(Network::class);
        $network->method('isInternational')->willReturn(true);
        $tlecController = $this->createMock(TlecController::class);
        $this->invokeMethod($tlecController, 'setInternationalStatusAndTimezoneFromContext', [$network]);

        // check timezone for international services is set to UTC
        $this->assertSame('UTC', ApplicationTime::getLocalTimeZone()->getName());
    }

    private function invokeMethod($object, string $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
