<?php
declare(strict_types = 1);
namespace Tests\App\Controller\FindByPid;

use App\Controller\FindByPid\SegmentController;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\CoreEntitiesService;
use PHPUnit\Framework\TestCase;

class SegmentControllerSegmentOwnerTest extends TestCase
{
    public function testHasNoContextWithNoItems()
    {
        $controller = new SegmentController();
        $mockService = $this->createMock(CoreEntitiesService::class);

        $items = [];
        $brandingContext = $controller->getSegmentOwner($items, $mockService);

        $this->assertNull($brandingContext);
    }

    public function testHasNoContextWithDiverseItems()
    {
        $controller = new SegmentController();
        $mockService = $this->createMock(CoreEntitiesService::class);

        $commonNetwork = $this->createMock(Network::class);
        $parent = $this->mockProgramme('b0000005', null);

        $items = [
            $this->mockProgramme('b0000001', null, $commonNetwork),
            $this->mockProgramme('b0000002', $parent, $commonNetwork),
            $this->mockProgramme('b0000003', $parent, null),
        ];

        $brandingContext = $controller->getSegmentOwner($items, $mockService);

        $this->assertNull($brandingContext);
    }


    public function testHasProgrammeContextWithCommonProgramme()
    {
        $controller = new SegmentController();

        $mockService = $this->mockServiceThatReturnsItemWithPid('b0000001');

        $commonNetwork = $this->createMock(Network::class);
        $parent = $this->mockProgramme('b0000005', null, $commonNetwork);
        $programme = $this->mockProgramme('b0000001', $parent, $commonNetwork);

        $items = [
            $programme,
            $programme,
            $programme,
        ];

        $brandingContext = $controller->getSegmentOwner($items, $mockService);

        $this->assertEquals('b0000001', $brandingContext->getPid());
    }

    public function testHasProgrammeContextWithCommonParentProgramme()
    {
        $controller = new SegmentController();
        $mockService = $this->mockServiceThatReturnsItemWithPid('b0000005');

        $commonNetwork = $this->createMock(Network::class);
        $parent = $this->mockProgramme('b0000005', null, $commonNetwork);

        $items = [
            $this->mockProgramme('b0000001', $parent, $commonNetwork),
            $this->mockProgramme('b0000002', $parent, $commonNetwork),
            $this->mockProgramme('b0000003', $parent, $commonNetwork),
        ];

        $brandingContext = $controller->getSegmentOwner($items, $mockService);

        $this->assertEquals('b0000005', $brandingContext->getPid());
    }


    public function testHasNetworkContextWithCommonNetwork()
    {
        $controller = new SegmentController();
        $mockService = $this->createMock(CoreEntitiesService::class);

        $commonNetwork = $this->createMock(Network::class);
        $parent = $this->mockProgramme('b0000005', null);

        $items = [
            $this->mockProgramme('b0000001', $parent, $commonNetwork),
            $this->mockProgramme('b0000002', $parent, $commonNetwork),
            $this->mockProgramme('b0000003', null, $commonNetwork),
        ];

        $brandingContext = $controller->getSegmentOwner($items, $mockService);

        $this->assertEquals($commonNetwork, $brandingContext);
    }

    private function mockServiceThatReturnsItemWithPid(string $expectedPid)
    {
        $mockProgramme = $this->createConfiguredMock(ProgrammeItem::class, [
            'getPid' => new Pid($expectedPid),
        ]);

        $mockService = $this->createMock(CoreEntitiesService::class);
        $mockService->expects($this->once())->method('findByPidFull')
            ->with(new Pid($expectedPid))
            ->willReturn($mockProgramme);

        return $mockService;
    }

    private function mockProgramme(string $pid, ?Programme $parent = null, ?Network $network = null)
    {
        $mock = $this->getMockBuilder(Programme::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPid', 'getParent', 'getNetwork'])
            ->getMock();

        $mock->method('getPid')->willReturn(new Pid($pid));
        $mock->method('getParent')->willReturn($parent);
        $mock->method('getNetwork')->willReturn($network);

        return $mock;
    }
}
