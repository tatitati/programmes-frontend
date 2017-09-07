<?php
declare(strict_types = 1);
namespace Tests\App\EventSubscriber;

use App\EventSubscriber\FixtureSubscriber;
use App\Fixture\ScenarioManagement\ScenarioNameManager;
use BBC\ProgrammesPagesService\Cache\Cache;
use BBC\ProgrammesPagesService\Cache\CacheInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class FixtureSubscriberTest extends TestCase
{
    private $container;

    private $scenarioNameManager;

    /** @var  FixtureSubscriber */
    private $fixtureSubscriber;

    public function setUp()
    {
        $this->scenarioNameManager = $this->createMock(ScenarioNameManager::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->fixtureSubscriber = new FixtureSubscriber($this->container);
    }

    public function testNonMasterRequest()
    {
        $request = $this->createMock(Request::class);
        $this->container->expects($this->never())->method('get');
        $this->container->expects($this->never())->method('set');

        $this->fixtureSubscriber->setupFixtureCode(
            $this->event($request, false)
        );
    }

    public function testNonScenarioRequest()
    {
        $request = $this->createMock(Request::class);
        $this->container->expects($this->any())
            ->method('get')
            ->with(ScenarioNameManager::class)
            ->willReturn($this->scenarioNameManager);

        $this->container->expects($this->never())->method('set');
        $this->scenarioNameManager->expects($this->once())->method('scenarioIsActive')->willReturn(false);
        $this->fixtureSubscriber->setupFixtureCode(
            $this->event($request, true)
        );
    }

    public function testCachingDisabledForScenarioRequest()
    {
        $request = $this->createMock(Request::class);
        $this->scenarioNameManager->expects($this->once())->method('scenarioIsActive')->willReturn(true);
        $expectedCache = new NullAdapter();
        $expectedProgrammesCache = new Cache($expectedCache, 'null');
        $expectedProgrammesCache->setFlushCacheItems(true);
        $this->container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($arg) use ($expectedCache) {
                switch ($arg) {
                    case ScenarioNameManager::class:
                        return $this->scenarioNameManager;
                    case 'cache.null':
                        return $expectedCache;
                }
                throw new InvalidArgumentException('something bad happened');
            }));
        $this->container->expects($this->exactly(4))
            ->method('set')
            ->withConsecutive(
                ['cache.programmes', $expectedCache],
                ['cache.app_page_chrome', $expectedCache],
                [Cache::class, $expectedProgrammesCache],
                [CacheInterface::class, $expectedProgrammesCache]
            );

        $this->fixtureSubscriber->setupFixtureCode(
            $this->event($request, true)
        );
    }

    private function event(Request $request, bool $isMasterRequest = true)
    {
        return new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            $isMasterRequest ? HttpKernelInterface::MASTER_REQUEST : HttpKernelInterface::SUB_REQUEST
        );
    }
}
