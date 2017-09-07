<?php
declare(strict_types = 1);

namespace App\Fixture\Doctrine;

use App\Fixture\ScenarioManagement\ScenarioNameManager;
use Doctrine\Bundle\DoctrineBundle\ConnectionFactory as DoctrineConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;

class ConnectionFactory extends DoctrineConnectionFactory
{
    /** @var ScenarioNameManager */
    private $scenarioManager;

    /** @var string */
    private $fixtureDbHost;

    /** @var string */
    private $fixtureDbPort;

    /** @var string */
    private $fixtureDbName;

    /** @var string */
    private $fixtureDbUser;

    /** @var string */
    private $fixtureDbPassword;

    public function __construct(
        array $typesConfig,
        ScenarioNameManager $scenarioManager,
        string $fixtureDbHost,
        string $fixtureDbPort,
        string $fixtureDbName,
        string $fixtureDbUser,
        string $fixtureDbPassword
    ) {
        parent::__construct($typesConfig);
        $this->scenarioManager = $scenarioManager;
        $this->fixtureDbHost = $fixtureDbHost;
        $this->fixtureDbPort = $fixtureDbPort;
        $this->fixtureDbName = $fixtureDbName;
        $this->fixtureDbUser = $fixtureDbUser;
        $this->fixtureDbPassword = $fixtureDbPassword;
    }

    public function createConnection(
        array $params,
        Configuration $config = null,
        EventManager $eventManager = null,
        array $mappingTypes = array()
    ) {
        if ($this->scenarioManager->scenarioIsActive()) {
            $params['host'] = $this->fixtureDbHost;
            $params['port'] = $this->fixtureDbPort;
            $params['dbname'] = $this->fixtureDbName;
            $params['user'] = $this->fixtureDbUser;
            $params['password'] = $this->fixtureDbPassword;
        }
        return parent::createConnection($params, $config, $eventManager, $mappingTypes);
    }
}
