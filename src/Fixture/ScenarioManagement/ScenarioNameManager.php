<?php
declare(strict_types = 1);

namespace App\Fixture\ScenarioManagement;

use App\ValueObject\CosmosInfo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use RuntimeException;

class ScenarioNameManager
{
    /** @var CosmosInfo */
    private $cosmosInfo;

    public function __construct(CosmosInfo $cosmosInfo)
    {
        $this->cosmosInfo = $cosmosInfo;
    }

    public function scenarioIsActive()
    {
        if ($this->isLowerEnvironment() && !empty($_GET['__scenario'])) {
            return true;
        }
        return false;
    }

    private function isLowerEnvironment()
    {
        return in_array($this->cosmosInfo->getAppEnvironment(), ['sandbox', 'int', 'test']);
    }
}
