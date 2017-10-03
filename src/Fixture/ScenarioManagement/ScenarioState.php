<?php
declare(strict_types = 1);

namespace App\Fixture\ScenarioManagement;

use App\Fixture\Exception\ScenarioReadingException;
use App\ValueObject\CosmosInfo;
use DateTimeImmutable;
use DateTime;
use App\Fixture\Exception\ScenarioGenerationException;
use Symfony\Component\HttpFoundation\RequestStack;

class ScenarioState
{
    private const NOT_GENERATING = 'none';
    private const GENERATING = 'generating';
    private const REGENERATING = 'regenerating';

    /** @var CosmosInfo */
    private $cosmosInfo;

    /** @var string|null */
    private $scenarioName;

    /** @var string */
    private $fixtureSecureKey;

    /** @var string */
    private $scenarioGeneratingMode = self::NOT_GENERATING;

    /** @var bool */
    private $scenarioDeletingMode = false;

    /** @var RequestStack */
    private $requestStack;

    public function __construct(CosmosInfo $cosmosInfo, RequestStack $requestStack, string $fixtureSecureKey)
    {
        $this->cosmosInfo = $cosmosInfo;
        $this->fixtureSecureKey = $fixtureSecureKey;
        $this->requestStack = $requestStack;
        $this->setState();
    }

    public function scenarioIsActive(): bool
    {
        return (bool) $this->scenarioName;
    }

    public function getActiveScenarioName(): ?string
    {
        return $this->scenarioName;
    }

    public function scenarioBrowseIsActive(): bool
    {
        return ($this->scenarioName === 'browse');
    }

    public function scenarioGenerationIsActive(): bool
    {
        return in_array($this->scenarioGeneratingMode, [self::GENERATING, self::REGENERATING]);
    }

    public function scenarioGenerationIsRegeneration(): bool
    {
        return ($this->scenarioGeneratingMode === self::REGENERATING);
    }

    public function scenarioGenerationIsSimpleGeneration(): bool
    {
        return ($this->scenarioGeneratingMode === self::GENERATING);
    }

    public function scenarioDeletionIsActive(): bool
    {
        return $this->scenarioDeletingMode;
    }

    public function getOverriddenApplicationTime(): ?int
    {
        $request = $this->requestStack->getMasterRequest();
        if (!$request) {
            return null;
        }
        if (!$this->scenarioIsActive() || empty($request->get('__scenario_time'))) {
            return null;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(Z|\+\d{2}:\d{2})$/', $request->get('__scenario_time'))) {
            $dateTime = DateTimeImmutable::createFromFormat(DateTime::ISO8601, $request->get('__scenario_time'));
            if ($dateTime) {
                return $dateTime->getTimestamp();
            }
        }
        throw new ScenarioReadingException("Invalid __scenario_time");
    }

    private function setState(): void
    {
        if (!$this->isLowerEnvironment()) {
            return;
        }
        $request = $this->requestStack->getMasterRequest();
        if (!$request) {
            return;
        }
        // Scenario name set?
        if (!empty($request->get('__scenario'))) {
            $scenarioName = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $request->get('__scenario'));
            $this->scenarioName = $scenarioName ? $scenarioName : null;
        }
        if (!$this->scenarioName) {
            return;
        }
        // Generation or deletion mode set?
        if (empty($request->get('__regenerate')) && empty($request->get('__generate')) && empty($request->get('__delete_scenario'))) {
            // Nope
            return;
        }
        if (!$this->passwordHeaderCorrectlySet()) {
            throw new ScenarioGenerationException("Scenario generation invoked without correct password");
        }
        // If we're here, we have a valid password and are on a lower env
        // Allow scenario generation/deletion
        if (!empty($request->get('__regenerate'))) {
            $this->scenarioGeneratingMode = self::REGENERATING;
        } elseif (!empty($request->get('__generate'))) {
            $this->scenarioGeneratingMode = self::GENERATING;
        } elseif (!empty($request->get('__delete_scenario'))) {
            $this->scenarioDeletingMode = true;
        }
    }

    private function isLowerEnvironment(): bool
    {
        return in_array($this->cosmosInfo->getAppEnvironment(), ['sandbox', 'int', 'test']);
    }

    private function passwordHeaderCorrectlySet(): bool
    {
        if ($this->fixtureSecureKey === 'blank') {
            // The bake script has this as a default. It's not a password
            return false;
        }
        $request = $this->requestStack->getMasterRequest();
        if (!$request) {
            return false;
        }
        return (
            $request->headers->has('X-PROGRAMMES-FIXTURE-SECURE-KEY')
            && trim($request->headers->get('X-PROGRAMMES-FIXTURE-SECURE-KEY')) === trim($this->fixtureSecureKey)
        );
    }
}
