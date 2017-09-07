<?php
declare(strict_types = 1);

namespace Tests\App\Fixture;

use App\Fixture\ScenarioManagement\ScenarioNameManager;
use App\ValueObject\CosmosInfo;
use PHPUnit\Framework\TestCase;

class ScenarioNameManagerTest extends TestCase
{
    public function tearDown()
    {
        unset($_GET['__scenario']);
    }

    public function testScenarioIsActive()
    {
        $cosmosInfo = new CosmosInfo('3.0', 'test');
        $scenarioNameManager = new ScenarioNameManager($cosmosInfo);
        $_GET['__scenario'] = 'hardouken';
        $this->assertTrue($scenarioNameManager->scenarioIsActive());
    }

    public function testScenarioIsNotActive()
    {
        $cosmosInfo = new CosmosInfo('3.0', 'test');
        $scenarioNameManager = new ScenarioNameManager($cosmosInfo);
        $this->assertFalse($scenarioNameManager->scenarioIsActive());
    }
    public function testScenarioIsNotActiveOnLive()
    {
        $cosmosInfo = new CosmosInfo('3.0', 'live');
        $scenarioNameManager = new ScenarioNameManager($cosmosInfo);
        $_GET['__scenario'] = 'hardouken';
        $this->assertFalse($scenarioNameManager->scenarioIsActive());
    }
}
