<?php
declare(strict_types=1);

namespace Tests\App\DsShared\Helpers;

use App\DsShared\Helpers\BroadcastNetworksHelper;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\ValueObject\Nid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Sid;
use Cake\Chronos\Chronos;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use RMP\Translate\TranslateFactory;

class BroadcastNetworksHelperTest extends TestCase
{
    /** @var BroadcastNetworksHelper */
    private $helper;

    public function setup()
    {
        $this->helper = new BroadcastNetworksHelper(new TranslateProvider(new TranslateFactory()));
    }

    public function testGetNetworksAndServicesDetailsWithOneNetworkShowingExceptServices(): void
    {
        $network1Services = $this->createServicesWithNetwork(['sid1', 'sid2', 'sid3'], 'nid1');

        $broadcast = $this->createCollapsedBroadcast(array_slice($network1Services, 0, 2));
        $breakdown = $this->helper->getNetworksAndServicesDetails($broadcast);

        $this->assertCount(1, $breakdown);
        $this->assertEquals('Network nid1', array_keys($breakdown)[0]);
        $this->assertEquals('except Service sid3', array_values($breakdown)[0]);
    }

    public function testGetNetworksAndServicesDetailsWithOneNetworkShowingOnlyServices(): void
    {
        $network1Services = $this->createServicesWithNetwork(['sid1', 'sid2', 'sid3', 'sid4'], 'nid1');

        $broadcast = $this->createCollapsedBroadcast(array_slice($network1Services, 0, 2));
        $breakdown = $this->helper->getNetworksAndServicesDetails($broadcast);

        $this->assertCount(1, $breakdown);
        $this->assertEquals('Network nid1', implode(array_keys($breakdown)));
        $this->assertEquals('Service sid1 & Service sid2 only', array_values($breakdown)[0]);
    }

    public function testGetNetworksAndServicesDetailsWithOneNetworkUsingAllServices(): void
    {
        $network1Services = $this->createServicesWithNetwork(['sid1', 'sid2', 'sid3'], 'nid1');

        $broadcast = $this->createCollapsedBroadcast($network1Services);
        $breakdown = $this->helper->getNetworksAndServicesDetails($broadcast);

        $this->assertCount(1, $breakdown);
        $this->assertEquals('Network nid1', array_keys($breakdown)[0]);
        $this->assertEquals('', array_values($breakdown)[0]);
    }

    public function testGetNetworksAndServicesDetailsWithTwoNetworks(): void
    {
        $network1Services = $this->createServicesWithNetwork(['sid1', 'sid2', 'sid3'], 'nid1');
        $network2Services = $this->createServicesWithNetwork(['sid4', 'sid5'], 'nid2');

        $broadcast = $this->createCollapsedBroadcast(array_merge(array_slice($network1Services, 0, 2), [$network2Services[0]]));
        $breakdown = $this->helper->getNetworksAndServicesDetails($broadcast);

        $this->assertCount(2, $breakdown);

        $this->assertEquals('Network nid1', array_keys($breakdown)[0]);
        $this->assertEquals(' & Network nid2', array_keys($breakdown)[1]);

        $this->assertEquals('except Service sid3', array_values($breakdown)[0]);
        $this->assertEquals('Service sid4', array_values($breakdown)[1]);
    }

    public function testGetNetworksAndServicesDetailsSeveralNetworks(): void
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

        $this->assertEquals('Network nid1', array_keys($breakdown)[0]);
        $this->assertEquals(', Network nid2', array_keys($breakdown)[1]);
        $this->assertEquals(', Network nid3', array_keys($breakdown)[2]);
        $this->assertEquals(', Network nid4', array_keys($breakdown)[3]);
        $this->assertEquals(', Network nid5', array_keys($breakdown)[4]);
        $this->assertEquals(' & 2 more', array_keys($breakdown)[5]);

        foreach ($breakdown as $key => $value) {
            $this->assertEquals('', $value);
        }
    }

    public function testGetNetworksAndServicesDetailsSeveralServices(): void
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

        $this->assertEquals('Network nid1', array_keys($breakdown)[0]);
        $this->assertEquals(
            'Service sid1, Service sid2, Service sid3, Service sid4, Service sid5 & 2 more only',
            array_values($breakdown)[0]
        );
    }

    private function createCollapsedBroadcast(array $services, string $startAt = ''): CollapsedBroadcast
    {
        $start = new Chronos($startAt);
        $end = new Chronos();
        $programmeItem = $this->createMock(ProgrammeItem::class);

        return new CollapsedBroadcast($programmeItem, $services, $start, $end, 30);
    }

    private function createServicesWithNetwork(array $sids, string $nid): array
    {
        $network = $this->createMock(Network::class);
        $network->method('getNid')->willReturn(new Nid($nid));
        $network->method('getName')->willReturn('Network ' . $nid);

        $services = [];

        foreach ($sids as $sid) {
            $service = $this->createMock(Service::class);
            $service->method('getSid')->willReturn(new Sid($sid));
            $service->method('getShortName')->willReturn('Service ' . $sid);
            $service->method('getNetwork')->willReturn($network);
            $services[] = $service;
        }

        $network->method('getServices')->willReturn($services);

        return $services;
    }
}
