<?php
declare(strict_types = 1);

namespace App\Fixture\ScenarioManagement;

use App\Fixture\Doctrine\Entity\HttpFixture;
use App\Fixture\Doctrine\Entity\Scenario;
use App\Fixture\Doctrine\EntityRepository\HttpFixtureRepository;
use App\Fixture\Doctrine\EntityRepository\ScenarioRepository;
use App\Fixture\Exception\ScenarioDeletedException;
use App\Fixture\Exception\ScenarioGenerationException;
use App\Fixture\Exception\ScenarioReadingException;
use App\Fixture\UrlMatching\EnvAgnosticUrl;
use App\Fixture\UrlMatching\UrlReverseEvangelist;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ScenarioManager
{
    /**
     * The time the fixture DB was created, 2017-09-01T11:30:00+00:00
     */
    public const DEFAULT_SCENARIO_TIME = 1504265400;

    /**  @var ScenarioState */
    private $scenarioState;

    /**  @var ScenarioRepository */
    private $scenarioRepository;

    /** @var HttpFixtureRepository */
    private $httpFixtureRepository;

    /** @var Scenario */
    private $scenario;

    /** @var HttpFixture[] */
    private $httpFixtures = [];

    /** @var UrlReverseEvangelist */
    private $urlReverseEvangelist;

    /** @var RequestStack */
    private $requestStack;

    public function __construct(
        ScenarioState $scenarioState,
        ScenarioRepository $scenarioRepository,
        HttpFixtureRepository $httpFixtureRepository,
        UrlReverseEvangelist $urlReverseEvangelist,
        RequestStack $requestStack
    ) {
        $this->scenarioState = $scenarioState;
        $this->scenarioRepository = $scenarioRepository;
        $this->httpFixtureRepository = $httpFixtureRepository;
        $this->urlReverseEvangelist = $urlReverseEvangelist;
        $this->requestStack = $requestStack;
    }

    public function scenarioIsActive(): bool
    {
        return $this->scenarioState->scenarioIsActive();
    }

    public function runAtStartOfRequest(): void
    {
        if (isset($this->scenario)) {
            return;
        }
        $scenarioName = $this->scenarioState->getActiveScenarioName();
        if ($this->scenarioState->scenarioDeletionIsActive()) {
            // We've been asked to delete a scenario
            $this->deleteActiveScenario();
            // Hack. We don't want to render the page. Let the handler catch this
            throw new ScenarioDeletedException("Scenario $scenarioName successfully deleted");
        }

        $scenario = $this->scenarioRepository->findByName($scenarioName);
        $httpFixtures = [];
        if ($scenario && $this->scenarioState->scenarioGenerationIsSimpleGeneration()) {
            throw new ScenarioGenerationException("Scenario $scenarioName already exists and cannot be generated");
        }
        if ($scenario) {
            // This is fine, loading an existing scenario successfully
            $this->setApplicationTime($scenario->getApplicationTime()->getTimestamp());
            $this->scenario = $scenario;
            $httpFixtures = $this->httpFixtureRepository->findAllByScenario($this->scenario);
        } elseif ($this->scenarioState->scenarioGenerationIsSimpleGeneration()) {
            // This is fine. We're trying to create a new scenario from scratch
            // Set the application time to the DB creation time by default and create a scenario object
            $this->setApplicationTime();
            $this->scenario = new Scenario($scenarioName, ApplicationTime::getTime(), $this->originalUrlFromRequest());
        } elseif ($this->scenarioState->scenarioBrowseIsActive()) {
            $this->setApplicationTime();
        } else {
            // Scenario not found when it should be found (reading or regenerating)
            throw new ScenarioReadingException("Scenario $scenarioName not found");
        }
        foreach ($httpFixtures as $httpFixture) {
            $this->httpFixtures[$httpFixture->getEnvAgnosticUrl()] = $httpFixture;
        }
    }

    public function runAtEndOfRequest(int $responseStatusCode): void
    {
        if (!$this->scenarioState->scenarioGenerationIsActive()) {
            return;
        }
        if (!$this->scenario) {
            throw new ScenarioGenerationException("Cannot save a scenario that hasn't been loaded yet");
        }
        if (in_array($responseStatusCode, [200, 301, 302])) {
            // Only save fixtures on successful page render
            $this->scenarioRepository->saveScenarioWithFixtures($this->scenario, array_values($this->httpFixtures));
        }
    }

    public function getHttpFixture(RequestInterface $request): ?Response
    {
        $url = (string) $request->getUri();
        if (!$this->urlReverseEvangelist->isFixturable($url)) {
            return null;
        }
        $envAgnosticUrl = $this->urlReverseEvangelist->makeEnvAgnostic($url);
        if (isset($this->httpFixtures[$envAgnosticUrl])) {
            return $this->makeResponse($this->httpFixtures[$envAgnosticUrl]);
        }

        if (!$this->scenarioState->scenarioGenerationIsActive() && !$this->scenarioState->scenarioBrowseIsActive()) {
            throw new ScenarioReadingException("Fixture for $url not found");
        }
        return null;
    }

    public function addHttpFixture(RequestInterface $request, Response $response): void
    {
        $url = (string) $request->getUri();
        if (!$this->scenarioState->scenarioGenerationIsActive() || !$this->urlReverseEvangelist->isFixturable($url)) {
            return;
        }
        $envAgnosticUrl = $this->urlReverseEvangelist->makeEnvAgnostic($url);
        $httpFixture = $this->makeHttpFixture($envAgnosticUrl, $response);
        $this->httpFixtures[$envAgnosticUrl] = $httpFixture;
    }

    private function deleteActiveScenario(): void
    {
        $scenarioName = $this->scenarioState->getActiveScenarioName();
        $scenario = $this->scenarioRepository->findByName($scenarioName);
        if (!$scenario) {
            throw new ScenarioGenerationException("Cannot delete scenario $scenarioName as it does not exist");
        }
        $this->scenarioRepository->deleteScenarioAndFixtures($scenario);
    }

    private function makeHttpFixture(string $url, Response $response): HttpFixture
    {
        $stringHeaders = [];
        foreach ($response->getHeaders() as $name => $value) {
            $stringHeaders[] = $name . ':' . $response->getHeaderLine($name);
        }
        $headers = join("\n", $stringHeaders);
        $httpFixture = new HttpFixture($url, $this->scenario);
        $bodyObject = $response->getBody();
        $body = $bodyObject->getContents();
        // If we don't "rewind" the body stream, nothing can access it after this...
        $bodyObject->rewind();
        $httpFixture->setBody($body);
        $httpFixture->setHeaders($headers);
        $httpFixture->setResponseCode($response->getStatusCode());
        return $httpFixture;
    }

    private function makeResponse(HttpFixture $httpFixture): Response
    {
        $headerArray = \GuzzleHttp\headers_from_lines(explode("\n", $httpFixture->getHeaders()));
        return new Response(
            $httpFixture->getResponseCode(),
            $headerArray,
            $httpFixture->getBody()
        );
    }

    private function setApplicationTime(int $time = null): void
    {
        if ($time === null) {
            $timeOverride = $this->scenarioState->getOverriddenApplicationTime();
            $time = ($timeOverride ?? self::DEFAULT_SCENARIO_TIME);
        }
        ApplicationTime::setTime($time);
    }

    /**
     * This gets and modifies the URL in Symfony's request object in order to save it in the database
     * alongside a scenario name. This is more for informational (to the tester) purposes than out
     * of any need for it in the code.
     */
    private function originalUrlFromRequest(): string
    {
        $request = $this->requestStack->getMasterRequest();
        $url = $request->getPathInfo();
        $query = $request->query;
        $queryString = '';
        if ($query->count()) {
            $query->remove('__generate');
            $query->remove('__regenerate');
            $query->remove('__scenario_time');
            $sep = '?';
            foreach ($query->all() as $key => $value) {
                $key = urlencode($key);
                $value = urlencode($value);
                $queryString .= "${sep}${key}=${value}";
                $sep = '&';
            }
        }
        return $url . $queryString;
    }
}
