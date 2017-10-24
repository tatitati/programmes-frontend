<?php
declare(strict_types = 1);

namespace Tests\App\Controller\FindByPid;

use App\Controller\FindByPid\TlecController;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;

class TlecControllerTest extends TestCase
{
    public function testIsVotePriority()
    {
        $controller = $this->createMock(TlecController::class);

        $programmeContainer = $this->createMock(ProgrammeContainer::class);

        $programmeContainer->expects($this->atLeastOnce())->method('getOption')
            ->will($this->returnValueMap([
                ['brand_layout', 'vote'],
                ['ivote_block', 'anythingthatisntnull'],
            ]));

        $this->assertTrue($this->invokeMethod($controller, 'isVotePriority', [$programmeContainer]));
    }

    /**
     * @dataProvider showMiniMapDataProvider
     */
    public function testShowMiniMap(Request $request, ProgrammeContainer $programmeContainer, bool $isPromoPriority)
    {
        $controller = $this->createMock(TlecController::class);

        $showMiniMap = $this->invokeMethod(
            $controller,
            'showMiniMap',
            [
                $request,
                $programmeContainer,
                $isPromoPriority,
            ]
        );
        $this->assertTrue($showMiniMap);
    }

    public function showMiniMapDataProvider(): array
    {
        $cases = [];
        $programmeContainer = $this->createMock(ProgrammeContainer::class);
        $cases['is-vote-priority'] = [new Request(), clone $programmeContainer, true];
        $cases['forced-by-url'] = [new Request(['__2016minimap' => 1]), clone $programmeContainer , false];

        $programmeContainer->expects($this->once())
            ->method('getOption')
            ->with('brand_2016_layout_use_minimap')
            ->willReturn('true');

        $cases['forced-by-url'] = [new Request(), $programmeContainer, false];

        return $cases;
    }

    public function testShowPromoPriority()
    {
        $controller = $this->createMock(TlecController::class);

        $programmeContainer = $this->createMock(ProgrammeContainer::class);
        $programmeContainer->method('isTlec')->willReturn(true);
        $programmeContainer->expects($this->once())
            ->method('getOption')
            ->with('brand_layout')
            ->willReturn('promo');
        $this->assertTrue($this->invokeMethod($controller, 'isPromoPriority', [$programmeContainer, false, true]));
    }

    private function invokeMethod($object, string $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
