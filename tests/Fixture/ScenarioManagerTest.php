<?php
declare(strict_types = 1);

namespace Tests\App\Fixture;

use App\Fixture\Doctrine\Entity\HttpFixture;
use App\Fixture\Doctrine\Entity\Scenario;
use App\Fixture\Doctrine\EntityRepository\HttpFixtureRepository;
use App\Fixture\Doctrine\EntityRepository\ScenarioRepository;
use App\Fixture\ScenarioManagement\ScenarioManager;
use App\Fixture\ScenarioManagement\ScenarioState;
use App\Fixture\UrlMatching\UrlReverseEvangelist;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use DateTime;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class ScenarioManagerTest extends TestCase
{
    private $scenarioState;

    private $scenarioRepository;

    private $httpFixtureRepository;

    private $urlReverseEvangelist;

    private $request;

    /** @var ScenarioManager */
    private $scenarioManager;

    public function setUp()
    {
        $this->scenarioState = $this->createMock(ScenarioState::class);
        $this->scenarioRepository = $this->createMock(ScenarioRepository::class);
        $this->httpFixtureRepository = $this->createMock(HttpFixtureRepository::class);
        $this->request = $this->createMock(Request::class);
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->any())->method('getMasterRequest')->willReturn($this->request);
        $this->urlReverseEvangelist = $this->createMock(UrlReverseEvangelist::class);
        $this->urlReverseEvangelist->expects($this->any())->method('makeEnvAgnostic')
            ->willReturnCallback(function () {
                // This returns the passed URL unmodified
                $args = func_get_args();
                return $args[0];
            });
        $this->scenarioManager = new ScenarioManager(
            $this->scenarioState,
            $this->scenarioRepository,
            $this->httpFixtureRepository,
            $this->urlReverseEvangelist,
            $requestStack
        );
    }

    public function tearDown()
    {
        ApplicationTime::setTime(null);
    }

    /**
     * @expectedException \App\Fixture\Exception\ScenarioDeletedException
     */
    public function testScenarioDeletion()
    {
        $this->scenarioState->expects($this->atLeastOnce())->method('getActiveScenarioName')->willReturn('scenario_of_geoff');
        $this->scenarioState->expects($this->atLeastOnce())->method('scenarioDeletionIsActive')->willReturn(true);
        $mockScenario = $this->createMock(Scenario::class);
        $this->scenarioRepository->expects($this->once())->method('findByName')->with('scenario_of_geoff')->willReturn($mockScenario);
        $this->scenarioRepository->expects($this->once())->method('deleteScenarioAndFixtures')->with($mockScenario);
        $this->scenarioManager->runAtStartOfRequest();
    }

    /**
     * @expectedException \App\Fixture\Exception\ScenarioGenerationException
     */
    public function testNonExistentScenarioDeletionThrows()
    {
        $this->scenarioState->expects($this->atLeastOnce())->method('getActiveScenarioName')->willReturn('scenario_of_geoff');
        $this->scenarioState->expects($this->atLeastOnce())->method('scenarioDeletionIsActive')->willReturn(true);
        $this->scenarioRepository->expects($this->once())->method('findByName')->with('scenario_of_geoff')->willReturn(null);
        $this->scenarioManager->runAtStartOfRequest();
    }

    /**
     * @expectedException \App\Fixture\Exception\ScenarioGenerationException
     */
    public function testGeneratingExistingScenarioThrows()
    {
        $mockScenario = $this->createMock(Scenario::class);
        $this->setGeneratingExpectations('scenario_of_geoff', $mockScenario);
        $this->scenarioManager->runAtStartOfRequest();
    }

    /**
     * @expectedException \App\Fixture\Exception\ScenarioReadingException
     */
    public function testScenarioNotFoundThrows()
    {
        $this->setReadingExpectations('scenario_of_geoff', null);
        $this->scenarioManager->runAtStartOfRequest();
    }

    public function testLoadingScenario()
    {
        $scenarioTime = DateTimeImmutable::createFromFormat(DateTime::ISO8601, '2011-09-01T11:30:00+00:00');
        $mockScenario = $this->createMock(Scenario::class);
        $mockScenario->expects($this->atLeastOnce())->method('getApplicationTime')->willReturn($scenarioTime);
        $this->setReadingExpectations('scenario_of_geoff', $mockScenario);
        $this->scenarioManager->runAtStartOfRequest();
        $this->assertEquals($scenarioTime, ApplicationTime::getTime());
        $this->scenarioRepository->expects($this->never())->method('saveScenarioWithFixtures');
        $this->scenarioManager->runAtEndOfRequest(200);
    }

    public function testBrowseScenario()
    {
        $this->setReadingExpectations('browse', null);
        $expectedTime = new DateTimeImmutable('@' . ScenarioManager::DEFAULT_SCENARIO_TIME);
        $this->scenarioManager->runAtStartOfRequest();
        $this->assertEquals($expectedTime, ApplicationTime::getTime());
    }

    public function testGeneratingAndSavingFixtures()
    {
        $this->setGeneratingExpectations('geoffs_favourite_scenario', null);
        $expectedTime = new DateTimeImmutable('@' . ScenarioManager::DEFAULT_SCENARIO_TIME);
        $this->urlReverseEvangelist->expects($this->atLeastOnce())->method('isFixturable')->willReturn(true);

        $this->request->expects($this->atLeastOnce())->method('getPathInfo')
            ->willReturn('http://www.test.bbc.co.uk/programmes');
        $this->request->query = new ParameterBag(['__scenario' => 'geoffs_favourite_scenario', '__generate' => 1, 'query' => 'string']);

        // Make some fake HTTP API request/responses and assert they are saved
        $expectedScenario = new Scenario(
            'geoffs_favourite_scenario',
            $expectedTime,
            'http://www.test.bbc.co.uk/programmes?__scenario=geoffs_favourite_scenario&query=string'
        );
        $url1 = 'https://branding.files.bbci.co.uk/branding/live/projects/br-00117.json';
        $expectedHttpFixture1 = new HttpFixture($url1, $expectedScenario);
        $expectedHttpFixture1->setHeaders('x-fish:Trout');
        $expectedHttpFixture1->setResponseCode(200);
        $expectedHttpFixture1->setBody('Some Branding');

        $url2 = 'https://navigation.api.bbci.co.uk/api';
        $expectedHttpFixture2 = new HttpFixture($url2, $expectedScenario);
        $expectedHttpFixture2->setResponseCode(200);
        $expectedHttpFixture2->setBody('The ORB');

        $this->scenarioRepository->expects($this->once())
            ->method('saveScenarioWithFixtures')
            ->with($expectedScenario, [$expectedHttpFixture1, $expectedHttpFixture2]);

        $request1 = new GuzzleRequest('GET', $url1);
        $response1 = new GuzzleResponse(200, ['x-fish' => 'Trout'], 'Some Branding');

        $request2 = new GuzzleRequest('GET', $url2);
        $response2 = new GuzzleResponse(200, [], 'The ORB');

        $this->scenarioManager->runAtStartOfRequest();
        $this->scenarioManager->addHttpFixture($request1, $response1);
        $this->scenarioManager->addHttpFixture($request2, $response2);
        $this->assertEquals($expectedTime, ApplicationTime::getTime());

        $this->scenarioManager->runAtEndOfRequest(200);
    }

    public function testGetHttpFixture()
    {
        $scenario = new Scenario('not_geoffs_favourite', ApplicationTime::getTime(), 'http://www.bbc.co.uk/programmes');
        $fixtureUrl = 'https://branding.files.bbci.co.uk/branding/live/projects/br-00117.json';
        $fixture1 = new HttpFixture($fixtureUrl, $scenario);
        $fixture1->setBody('Some Branding');
        $fixture1->setResponseCode(200);
        $fixture1->setHeaders('x-fish:Haddock');

        $this->setReadingExpectations('not_geoffs_favourite', $scenario, [$fixture1]);
        $this->urlReverseEvangelist->expects($this->atLeastOnce())->method('isFixturable')->willReturn(true);
        $this->scenarioManager->runAtStartOfRequest();
        $expectedResponse = new GuzzleResponse(200, ['x-fish' => 'Haddock'], 'Some Branding');
        $mockRequest = new GuzzleRequest('GET', $fixtureUrl);

        $realResponse = $this->scenarioManager->getHttpFixture($mockRequest);
        $this->assertEquals($expectedResponse->getStatusCode(), $realResponse->getStatusCode());
        $this->assertEquals($expectedResponse->getHeaders(), $realResponse->getHeaders());
        $this->assertEquals($expectedResponse->getBody()->getContents(), $realResponse->getBody()->getContents());
    }

    /**
     * @expectedException \App\Fixture\Exception\ScenarioReadingException
     */
    public function testGetHttpFixtureThrowsForGeneratingWhenNotFound()
    {
        $scenario = new Scenario('not_geoffs_favourite', ApplicationTime::getTime(), 'http://www.bbc.co.uk/programmes');

        $this->setReadingExpectations('not_geoffs_favourite', $scenario, []);
        $this->urlReverseEvangelist->expects($this->atLeastOnce())->method('isFixturable')->willReturn(true);
        $this->scenarioManager->runAtStartOfRequest();
        $this->scenarioManager->getHttpFixture(new GuzzleRequest('GET', 'http://something.somewhere'));
    }

    public function testGetHttpFixtureDoesNotThrowForGenerating()
    {
        $scenarioName = 'not_geoffs_favourite';
        $scenario = new Scenario($scenarioName, ApplicationTime::getTime(), 'http://www.bbc.co.uk/programmes');
        $this->scenarioState->expects($this->atLeastOnce())->method('getActiveScenarioName')->willReturn($scenarioName);
        $this->scenarioState->expects($this->any())->method('scenarioDeletionIsActive')->willReturn(false);
        $this->scenarioState->expects($this->any())->method('scenarioGenerationIsSimpleGeneration')->willReturn(false);
        $this->scenarioState->expects($this->any())->method('scenarioGenerationIsActive')->willReturn(true);
        $this->scenarioState->expects($this->any())->method('scenarioBrowseIsActive')->willReturn(($scenarioName === 'browse'));
        $this->scenarioRepository->expects($this->atLeastOnce())->method('findByName')->with($scenarioName)->willReturn($scenario);
        $this->httpFixtureRepository->expects($this->any())->method('findAllByScenario')->with($scenario)->willReturn([]);
        $this->urlReverseEvangelist->expects($this->atLeastOnce())->method('isFixturable')->willReturn(true);
        $this->scenarioManager->runAtStartOfRequest();
        $this->assertNull($this->scenarioManager->getHttpFixture(new GuzzleRequest('GET', 'http://something.somewhere')));
    }

    private function setReadingExpectations($scenarioName, $scenario, $httpFixtures = [])
    {
        $this->scenarioState->expects($this->atLeastOnce())->method('getActiveScenarioName')->willReturn($scenarioName);
        $this->scenarioState->expects($this->any())->method('scenarioDeletionIsActive')->willReturn(false);
        $this->scenarioState->expects($this->any())->method('scenarioGenerationIsSimpleGeneration')->willReturn(false);
        $this->scenarioState->expects($this->any())->method('scenarioGenerationIsActive')->willReturn(false);
        $this->scenarioState->expects($this->any())->method('scenarioBrowseIsActive')->willReturn(($scenarioName === 'browse'));
        $this->scenarioRepository->expects($this->atLeastOnce())->method('findByName')->with($scenarioName)->willReturn($scenario);
        $this->httpFixtureRepository->expects($this->any())->method('findAllByScenario')->with($scenario)->willReturn($httpFixtures);
    }

    private function setGeneratingExpectations($scenarioName, $scenario)
    {
        $this->scenarioState->expects($this->atLeastOnce())->method('getActiveScenarioName')->willReturn($scenarioName);
        $this->scenarioState->expects($this->any())->method('scenarioDeletionIsActive')->willReturn(false);
        $this->scenarioState->expects($this->any())->method('scenarioGenerationIsSimpleGeneration')->willReturn(true);
        $this->scenarioState->expects($this->any())->method('scenarioGenerationIsActive')->willReturn(true);
        $this->scenarioRepository->expects($this->any())->method('findByName')->with($scenarioName)->willReturn($scenario);
    }
}
