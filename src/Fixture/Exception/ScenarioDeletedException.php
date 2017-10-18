<?php
declare(strict_types = 1);

namespace App\Fixture\Exception;

/**
 * Hack to avoid rendering a page when deleting a scenario
 */
class ScenarioDeletedException extends ScenarioGenerationException
{

}
