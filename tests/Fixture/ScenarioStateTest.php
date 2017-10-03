<?php
declare(strict_types = 1);

namespace Tests\App\Fixture;

use App\Fixture\Exception\ScenarioReadingException;
use App\Fixture\ScenarioManagement\ScenarioState;
use App\ValueObject\CosmosInfo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ScenarioStateTest extends TestCase
{
    private $request;

    private $requestStack;

    public function setUp()
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->request = $this->createMock(Request::class);
        $this->requestStack->expects($this->any())
            ->method('getMasterRequest')
            ->willReturn($this->request);
    }

    public function testScenarioIsActiveWhenItShouldBe()
    {
        $cosmosInfo = new CosmosInfo('3.0', 'test');
        $this->setMockRequestValues('hardouken');
        $scenarioState = new ScenarioState($cosmosInfo, $this->requestStack, 'supersecretkey');
        $this->assertTrue($scenarioState->scenarioIsActive());
    }

    public function testScenarioIsNotActiveWhenNoQueryString()
    {
        $cosmosInfo = new CosmosInfo('3.0', 'test');
        $this->setMockRequestValues(null);
        $scenarioState = new ScenarioState($cosmosInfo, $this->requestStack, 'supersecretkey');
        $this->assertFalse($scenarioState->scenarioIsActive());
    }

    public function testScenarioIsNotActiveOnLive()
    {
        $this->setMockRequestValues('hardouken');
        $cosmosInfo = new CosmosInfo('3.0', 'live');
        $scenarioState = new ScenarioState($cosmosInfo, $this->requestStack, 'supersecretkey');

        $this->assertFalse($scenarioState->scenarioIsActive());
    }

    public function testGenerationAndDeletionNotActiveInBaseState()
    {
        $cosmosInfo = new CosmosInfo('3.0', 'test');
        $scenarioState = new ScenarioState($cosmosInfo, $this->requestStack, 'supersecretkey');

        $this->assertFalse($scenarioState->scenarioGenerationIsActive());
        $this->assertFalse($scenarioState->scenarioDeletionIsActive());
    }

    public function testGenerationActiveSimple()
    {
        $cosmosInfo = new CosmosInfo('3.0', 'test');
        $this->setMockRequestValues('hardouken', 1, null, 'supersecretkey');
        $scenarioState = new ScenarioState($cosmosInfo, $this->requestStack, 'supersecretkey');
        $this->assertTrue($scenarioState->scenarioGenerationIsActive());
        $this->assertFalse($scenarioState->scenarioGenerationIsRegeneration());
        $this->assertTrue($scenarioState->scenarioGenerationIsSimpleGeneration());
        $this->assertEquals('hardouken', $scenarioState->getActiveScenarioName());
    }

    public function testGenerationNotActiveOnLive()
    {
        $cosmosInfo = new CosmosInfo('3.0', 'live');
        $this->setMockRequestValues('hardouken', 1, null, 'supersecretkey');
        $scenarioState = new ScenarioState($cosmosInfo, $this->requestStack, 'supersecretkey');
        $this->assertFalse($scenarioState->scenarioGenerationIsActive());
    }

    /**
     * @expectedException \App\Fixture\Exception\ScenarioGenerationException
     */
    public function testGenerationThrowsOnIncorrectPassword()
    {
        $cosmosInfo = new CosmosInfo('3.0', 'test');
        $this->setMockRequestValues('hardouken', 1, null, 'supersecretkey');
        $scenarioState = new ScenarioState($cosmosInfo, $this->requestStack, 'evilh4x0rkey');
    }


    public function testRegeneration()
    {
        $cosmosInfo = new CosmosInfo('3.0', 'test');
        $this->setMockRequestValues('hardouken', null, 1, 'supersecretkey');
        $scenarioState = new ScenarioState($cosmosInfo, $this->requestStack, 'supersecretkey');
        $this->assertTrue($scenarioState->scenarioGenerationIsActive());
        $this->assertTrue($scenarioState->scenarioGenerationIsRegeneration());
        $this->assertFalse($scenarioState->scenarioGenerationIsSimpleGeneration());
    }

    public function testDeletion()
    {
        $cosmosInfo = new CosmosInfo('3.0', 'test');
        $this->setMockRequestValues('hardouken', null, null, 'supersecretkey', 1);
        $scenarioState = new ScenarioState($cosmosInfo, $this->requestStack, 'supersecretkey');
        $this->assertTrue($scenarioState->scenarioDeletionIsActive());
    }

    /**
     * @dataProvider overriddenApplicationTimeProvider
     */
    public function testGetOverriddenApplicationTime($input, $expected)
    {
        $cosmosInfo = new CosmosInfo('3.0', 'test');
        $this->setMockRequestValues('hardouken', 1, null, 'supersecretkey', null, $input);
        $scenarioState = new ScenarioState($cosmosInfo, $this->requestStack, 'supersecretkey');
        $this->assertEquals($expected, $scenarioState->getOverriddenApplicationTime());
    }

    public function overriddenApplicationTimeProvider()
    {
        return [
            ['2017-09-01T11:30:00Z', \DateTimeImmutable::createFromFormat(\DateTime::ISO8601, '2017-09-01T11:30:00Z')->getTimestamp()],
            ['2010-01-29T23:30:00+01:00', \DateTimeImmutable::createFromFormat(\DateTime::ISO8601, '2010-01-29T23:30:00+01:00')->getTimestamp()],
            [null, null],
        ];
    }

    /**
     * @expectedException \App\Fixture\Exception\ScenarioReadingException
     */
    public function testInvalidApplicationTimeThrows()
    {
        $cosmosInfo = new CosmosInfo('3.0', 'test');
        $this->setMockRequestValues('hardouken', 1, null, 'supersecretkey', null, '2010-01-29T23:30:00');
        $scenarioState = new ScenarioState($cosmosInfo, $this->requestStack, 'supersecretkey');
        $scenarioState->getOverriddenApplicationTime();
    }

    private function setMockRequestValues(
        $scenarioName,
        $generate = null,
        $regenerate = null,
        $headerKey = null,
        $delete = null,
        $scenarioTime = null
    ) {
        //returnValueMap b0rks horribly here for no reason I can name
        $this->request->expects($this->any())->method('get')
            ->will($this->returnCallback(function () use ($scenarioName, $generate, $regenerate, $headerKey, $delete, $scenarioTime) {
                $args = func_get_args();
                $paramName = $args[0];
                $map = [
                    '__scenario' => $scenarioName,
                    '__generate' => $generate,
                    '__regenerate' => $regenerate,
                    '__delete_scenario' => $delete,
                    '__scenario_time' => $scenarioTime,
                ];
                return $map[$paramName];
            }));

        $headerStack = $this->createMock(HeaderBag::class);
        $headerStack->expects($this->any())
            ->method('has')
            ->with('X-PROGRAMMES-FIXTURE-SECURE-KEY')
            ->willReturn($headerKey ? true : false);
        $headerStack->expects($this->any())
            ->method('get')
            ->with('X-PROGRAMMES-FIXTURE-SECURE-KEY')
            ->willReturn($headerKey);
        $this->request->headers = $headerStack;
    }
}
