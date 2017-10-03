<?php
declare(strict_types = 1);
namespace Tests\App\EventSubscriber;

use App\EventSubscriber\FixtureSubscriber;
use App\Fixture\ScenarioManagement\ScenarioManager;
use App\Fixture\ScenarioManagement\ScenarioState;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class FixtureSubscriberTest extends TestCase
{
    private $container;

    private $scenarioState;

    private $scenarioManager;

    /** @var  FixtureSubscriber */
    private $fixtureSubscriber;

    public function setUp()
    {
        $this->scenarioState = $this->createMock(ScenarioState::class);
        $this->scenarioManager = $this->createMock(ScenarioManager::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->container->expects($this->any())->method('get')->will($this->returnValueMap([
            [ScenarioManager::class, $this->scenarioManager],
            [ScenarioState::class, $this->scenarioState],
        ]));
        $this->fixtureSubscriber = new FixtureSubscriber($this->container);
    }

    public function testNonMasterRequest()
    {
        $request = $this->createMock(Request::class);
        $this->container->expects($this->never())->method('get');
        $this->scenarioManager->expects($this->never())->method('runAtStartOfRequest');
        $this->scenarioManager->expects($this->never())->method('runAtEndOfRequest');

        $this->fixtureSubscriber->runAtStartOfRequest(
            $this->getResponseEvent($request, false)
        );
    }

    public function testNonScenarioRequest()
    {
        $request = $this->createMock(Request::class);

        $this->scenarioState->expects($this->atLeastOnce())->method('scenarioIsActive')->willReturn(false);
        $this->scenarioManager->expects($this->never())->method('runAtStartOfRequest');
        $this->scenarioManager->expects($this->never())->method('runAtEndOfRequest');
        $this->fixtureSubscriber->runAtStartOfRequest(
            $this->getResponseEvent($request, true)
        );
        $response = $this->createMock(Response::class);
        $response->expects($this->never())->method('getStatusCode');
        $this->fixtureSubscriber->runAtEndOfRequest(
            $this->getPostResponseEvent($response)
        );
    }

    public function testScenarioRequest()
    {
        $request = $this->createMock(Request::class);

        $this->scenarioState->expects($this->atLeastOnce())->method('scenarioIsActive')->willReturn(true);
        $this->scenarioManager->expects($this->once())->method('runAtStartOfRequest');
        $this->scenarioManager->expects($this->once())->method('runAtEndOfRequest')->with(200);
        $this->fixtureSubscriber->runAtStartOfRequest(
            $this->getResponseEvent($request, true)
        );
        $response = $this->createMock(Response::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn(200);
        $this->fixtureSubscriber->runAtEndOfRequest(
            $this->getPostResponseEvent($response)
        );
    }

    private function getResponseEvent(Request $request, bool $isMasterRequest = true)
    {
        return new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            $isMasterRequest ? HttpKernelInterface::MASTER_REQUEST : HttpKernelInterface::SUB_REQUEST
        );
    }

    private function getPostResponseEvent(Response $response)
    {
        return new PostResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $this->createMock(Request::class),
            $response
        );
    }
}
