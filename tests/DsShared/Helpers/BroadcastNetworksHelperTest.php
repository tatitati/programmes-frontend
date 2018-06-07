<?php
declare(strict_types=1);

namespace Tests\App\DsShared\Helpers;

use App\DsShared\Helpers\BroadcastNetworksHelper;
use App\Translate\TranslateProvider;
use App\ValueObject\BroadcastNetworkBreakdown;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\ValueObject\Nid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Sid;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;
use RMP\Translate\TranslateFactory;

class BroadcastNetworksHelperTest extends TestCase
{
    /** @var BroadcastNetworksHelper */
    private $helper;

    public function setup()
    {
        $this->helper = new BroadcastNetworksHelper(new TranslateProvider(new TranslateFactory()));
    }

    public function testGetNetworksAndServicesDetailsWithServiceExceptionWithSameNameAsNetworkGetsIgnored()
    {
        // In this test, 'nid1' is the only network that doesn't get the broadcast. As it has
        // the same name as the network, it gets ignored, which leads to no qualifier for the
        // network
        $services = $this->createServicesWithNetwork(
            ['nid1', 'sid1', 'sid2', 'sid3'],
            'nid1',
            false
        );

        $broadcast = $this->createCollapsedBroadcast(array_slice($services, 1));
        $breakdown = $this->helper->getNetworksAndServicesDetails($broadcast);

        $this->assertCount(1, $breakdown);
        $this->assertEquals('nid1', array_reduce(
            $breakdown,
            function (string $res, BroadcastNetworkBreakdown $b) {
                return $res . $b->getNetworkName();
            },
            ''
        ));
        $this->assertEmpty(array_reduce(
            $breakdown,
            function (string $res, BroadcastNetworkBreakdown $b) {
                return $res . $b->getServicesNames();
            },
            ''
        ));
    }

    public function testGetNetworksAndServicesDetailsWithServiceWithSameNameAsNetworkGetsIgnored()
    {
        // In this test, 'nid1' is the only network that doesn't get the broadcast. As it has
        // the same name as the network, it gets ignored, which leads to no qualifier for the
        // network
        $services = $this->createServicesWithNetwork(
            ['nid1', 'sid1', 'sid2', 'sid3'],
            'nid1',
            false
        );

        $broadcast = $this->createCollapsedBroadcast(array_slice($services, 0, 1));
        $breakdown = $this->helper->getNetworksAndServicesDetails($broadcast);

        $this->assertCount(1, $breakdown);
        $this->assertEquals('nid1', array_reduce(
            $breakdown,
            function (string $res, BroadcastNetworkBreakdown $b) {
                return $res . $b->getNetworkName();
            },
            ''
        ));
        $this->assertEmpty(array_reduce(
            $breakdown,
            function (string $res, BroadcastNetworkBreakdown $b) {
                return $res . $b->getServicesNames();
            },
            ''
        ));
    }

    public function testGetNetworksAndServicesDetailsWithOneNetworkShowingExceptServices()
    {
        $network1Services = $this->createServicesWithNetwork(['sid1', 'sid2', 'sid3'], 'nid1');

        $broadcast = $this->createCollapsedBroadcast(array_slice($network1Services, 0, 2));
        $breakdown = $this->helper->getNetworksAndServicesDetails($broadcast);

        $this->assertCount(1, $breakdown);
        $this->assertEquals('Network nid1', array_reduce(
            $breakdown,
            function (string $res, BroadcastNetworkBreakdown $b) {
                return $res . $b->getNetworkName();
            },
            ''
        ));
        $this->assertEquals('except Service sid3', array_reduce(
            $breakdown,
            function (string $res, BroadcastNetworkBreakdown $b) {
                return $res . $b->getServicesNames();
            },
            ''
        ));
    }

    public function testGetNetworksAndServicesDetailsWithOneNetworkShowingOnlyServices()
    {
        $network1Services = $this->createServicesWithNetwork(['sid1', 'sid2', 'sid3', 'sid4'], 'nid1');

        $broadcast = $this->createCollapsedBroadcast(array_slice($network1Services, 0, 2));
        $breakdown = $this->helper->getNetworksAndServicesDetails($broadcast);

        $this->assertCount(1, $breakdown);
        $this->assertEquals('Network nid1', array_reduce(
            $breakdown,
            function (string $res, BroadcastNetworkBreakdown $b) {
                return $res . $b->getNetworkName();
            },
            ''
        ));
        $this->assertEquals('Service sid1 & Service sid2 only', $breakdown[0]->getServicesNames());
    }

    public function testGetNetworksAndServicesDetailsWithOneNetworkUsingAllServices()
    {
        $network1Services = $this->createServicesWithNetwork(['sid1', 'sid2', 'sid3'], 'nid1');

        $broadcast = $this->createCollapsedBroadcast($network1Services);
        $breakdown = $this->helper->getNetworksAndServicesDetails($broadcast);

        $this->assertCount(1, $breakdown);
        $this->assertEquals('Network nid1', $breakdown[0]->getNetworkName());
        $this->assertEquals('', $breakdown[0]->getServicesNames());
    }

    public function testGetNetworksAndServicesDetailsWithTwoNetworks()
    {
        $network1Services = $this->createServicesWithNetwork(['sid1', 'sid2', 'sid3'], 'nid1');
        $network2Services = $this->createServicesWithNetwork(['sid4', 'sid5'], 'nid2');

        $broadcast = $this->createCollapsedBroadcast(array_merge(array_slice($network1Services, 0, 2), [$network2Services[0]]));
        $breakdown = $this->helper->getNetworksAndServicesDetails($broadcast);

        $this->assertCount(2, $breakdown);

        $this->assertEquals('Network nid1', $breakdown[0]->getNetworkName());
        $this->assertEquals(' & Network nid2', $breakdown[1]->getNetworkName());

        $this->assertEquals('except Service sid3', $breakdown[0]->getServicesNames());
        $this->assertEquals('Service sid4', $breakdown[1]->getServicesNames());
    }

    public function testGetNetworksAndServicesDetailsSeveralNetworks()
    {
        $s1 = $this->createServicesWithNetwork(['sid1'], 'nid1');
        $s2 = $this->createServicesWithNetwork(['sid2'], 'nid2');
        $s3 = $this->createServicesWithNetwork(['sid3'], 'nid3');
        $s4 = $this->createServicesWithNetwork(['sid4'], 'nid4');
        $s5 = $this->createServicesWithNetwork(['sid5'], 'nid5');
        $s6 = $this->createServicesWithNetwork(['sid6'], 'nid6');
        $s7 = $this->createServicesWithNetwork(['sid7'], 'nid7');

        $broadcast = $this->createCollapsedBroadcast(array_merge($s1, $s2, $s3, $s4, $s5, $s6, $s7));
        $breakdown = $this->helper->getNetworksAndServicesDetails($broadcast);

        $this->assertCount(6, $breakdown);

        $this->assertEquals('Network nid1', $breakdown[0]->getNetworkName());
        $this->assertEquals(', Network nid2', $breakdown[1]->getNetworkName());
        $this->assertEquals(', Network nid3', $breakdown[2]->getNetworkName());
        $this->assertEquals(', Network nid4', $breakdown[3]->getNetworkName());
        $this->assertEquals(', Network nid5', $breakdown[4]->getNetworkName());
        $this->assertNull($breakdown[5]->getNetwork());
        $this->assertEquals(' & 2 more', $breakdown[5]->getNetworkName());

        foreach ($breakdown as $b) {
            $this->assertEquals('', $b->getServicesNames());
        }
    }

    public function testGetNetworksAndServicesDetailsSeveralServices()
    {
        $services = $this->createServicesWithNetwork([
            'sid1',
            'sid2',
            'sid3',
            'sid4',
            'sid5',
            'sid6',
            'sid7',
            'sid8',
            'sid9',
            'sid10',
            'sid11',
            'sid12',
            'sid13',
            'sid14',
        ], 'nid1');

        $broadcast = $this->createCollapsedBroadcast(array_slice($services, 0, 7));
        $breakdown = $this->helper->getNetworksAndServicesDetails($broadcast);

        $this->assertCount(1, $breakdown);

        $this->assertEquals('Network nid1', $breakdown[0]->getNetworkName());
        $this->assertEquals(
            'Service sid1, Service sid2, Service sid3, Service sid4, Service sid5 & 2 more only',
            $breakdown[0]->getServicesNames()
        );
    }

    private function createCollapsedBroadcast(array $services, string $startAt = ''): CollapsedBroadcast
    {
        $start = new Chronos($startAt);
        $end = new Chronos();
        $programmeItem = $this->createMock(ProgrammeItem::class);

        return new CollapsedBroadcast($programmeItem, $services, $start, $end, 30);
    }

    private function createServicesWithNetwork(array $sids, string $nid, bool $usePrefix = true): array
    {
        $network = $this->createMock(Network::class);
        $network->method('getNid')->willReturn(new Nid($nid));
        $prefix = $usePrefix ? 'Network ' : '';
        $network->method('getName')->willReturn($prefix . $nid);

        $services = [];

        foreach ($sids as $sid) {
            $service = $this->createMock(Service::class);
            $service->method('getSid')->willReturn(new Sid($sid));
            $prefix = $usePrefix ? 'Service ' : '';
            $service->method('getShortName')->willReturn($prefix . $sid);
            $service->method('getNetwork')->willReturn($network);
            $services[] = $service;
        }

        $network->method('getServices')->willReturn($services);

        return $services;
    }
}
