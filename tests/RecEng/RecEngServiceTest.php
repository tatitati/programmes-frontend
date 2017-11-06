<?php
declare(strict_types = 1);

namespace Tests\App\RecEng;

use App\RecEng\RecEngService;
use BBC\ProgrammesPagesService\Cache\CacheInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use BBC\ProgrammesPagesService\Service\ProgrammesAggregationService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @covers App\RecEng\RecEngService
 */
class RecEngServiceTest extends TestCase
{
    /** @var Client */
    private $client;

    /** @var MockHandler */
    private $mockHandler;

    /** @var ProgrammesService|PHPUnit_Framework_MockObject_MockObject */
    private $mockProgrammesService;

    /** @var UrlGeneratorInterface */
    private $mockRouter;

    private $guzzleRequestContainer;

    /** @var LoggerInterface */
    private $mockLogger;

    /** @var CacheInterface */
    private $mockCache;

    public function setUp()
    {
        $this->mockRouter = $this->createMock(UrlGeneratorInterface::class);

        $this->guzzleRequestContainer = [];

        $this->mockHandler = new MockHandler();
        $stack = HandlerStack::create($this->mockHandler);

        $history = Middleware::history($this->guzzleRequestContainer);
        $stack->push($history);

        $this->client = new Client(['handler' => $stack]);

        $this->mockProgrammesService = $this->createMock(ProgrammesService::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);
        $this->mockCache = $this->createMock(CacheInterface::class);
    }

    public function testParsesRecEngResponseCorrectly()
    {
        $response = ['recommendations' => [['ref' => 'urn:bbc:pips:p04vjd23'], ['ref' => 'urn:bbc:pips:p0576pm5']]];
        $this->queueMockResponse(200, [], json_encode($response));

        $mockEpisode = $this->createMock(Episode::class);
        $mockRecProgrammeOne = $this->createMock(Programme::class);
        $mockRecProgrammeTwo = $this->createMock(Programme::class);

        $mockRecProgrammeOne->method('getPid')->willReturn(new Pid('p04vjd23'));
        $mockRecProgrammeTwo->method('getPid')->willReturn(new Pid('p0576pm5'));

        $programmesServiceResult = [$mockRecProgrammeOne, $mockRecProgrammeTwo];
        $this->mockProgrammesService
            ->expects($this->once())
            ->method('findByPids')
            ->with([new Pid('p04vjd23'), new Pid('p0576pm5')])
            ->willReturn($programmesServiceResult);

        $recEng = $this->createMockRecEng();
        $result = $recEng->getRecommendations($mockEpisode, null, null, null, 2);

        $this->assertContainsOnlyInstancesOf(Programme::class, $result);
        $this->assertContains('p04vjd23', [$result[0]->getPid(), $result[1]->getPid()]);
        $this->assertContains('p0576pm5', [$result[0]->getPid(), $result[1]->getPid()]);
    }

    /** @dataProvider isVideoOrAudioProvider */
    public function testUsesCorrectRecEngApiKey(bool $isVideo, string $expectedKey)
    {
        $response = ['recommendations' => [['ref' => 'urn:bbc:pips:p04vjd23'], ['ref' => 'urn:bbc:pips:p0576pm5']]];
        $this->queueMockResponse(200, [], json_encode($response));

        $mockEpisode = $this->createMock(Episode::class);
        $mockEpisode->method('isVideo')->willReturn($isVideo);

        $this->mockProgrammesService->method('findByPids')->willReturn([$this->createMock(Programme::class)]);
        $this->mockRouter
            ->expects($this->once())
            ->method('generate')
            ->with('receng', array('key' => $expectedKey, 'id' => ''))
            ->willReturn('http://somedomain.co.uk?key=' . $expectedKey);

        $recEng = $this->createMockRecEng();
        $result = $recEng->getRecommendations($mockEpisode, null, null, null, 2);

        $requestQueryString = $this->guzzleRequestContainer[0]['request']->getUri()->getQuery();
        $this->assertContains($expectedKey, $requestQueryString);
    }

    public function isVideoOrAudioProvider(): array
    {
        return [
            'video' => [true, 'imASecretVideoKey'],
            'audio' => [false, 'imASecretAudioKey'],
        ];
    }

    public function testResultLimit()
    {
        $response = ['recommendations' => [['ref' => 'urn:bbc:pips:p04vjd23'], ['ref' => 'urn:bbc:pips:p0576pm5']]];
        $this->queueMockResponse(200, [], json_encode($response));

        $mockEpisode = $this->createMock(Episode::class);

        $mockRecProgrammeOne = $this->createMock(Programme::class);
        $mockRecProgrammeOne->method('getPid')->willReturn(new Pid('p04vjd23'));

        $programmesServiceResult = [$mockRecProgrammeOne];
        $this->mockProgrammesService
            ->expects($this->once())
            ->method('findByPids')
            ->with([new Pid('p04vjd23')])
            ->willReturn($programmesServiceResult);

        $recEng = $this->createMockRecEng();
        $result = $recEng->getRecommendations($mockEpisode, null, null, null, 1);

        $this->assertContainsOnlyInstancesOf(Programme::class, $result);
        $this->assertContains('p04vjd23', [$result[0]->getPid()]);
    }

    public function testReturnsEmptyArrayForResponseWithNoRecommendations()
    {
        $this->queueMockResponse(200, [], "{\"recommendations\":[]}");

        $mockEpisode = $this->createMock(Episode::class);
        $this->mockProgrammesService->method('findByPids')->willReturn([]);

        $recEng = $this->createMockRecEng();
        $result = $recEng->getRecommendations($mockEpisode, null, null, null, 2);

        $this->assertEquals([], $result);
    }

    public function testReturnsEmptyArrayForEmptyResponse()
    {
        $this->queueMockResponse(200, []);

        $mockEpisode = $this->createMock(Episode::class);
        $this->mockProgrammesService->method('findByPids')->willReturn([]);

        $recEng = $this->createMockRecEng();
        $result = $recEng->getRecommendations($mockEpisode, null, null, null, 2);

        $this->assertEquals([], $result);
    }

    public function testReturnsEmptyArrayForInvalidPids()
    {
        $response = ['recommendations' => [['ref' => 'urn:bbc:pips:invalidpid'], ['ref' => 'urn:bbc:pips:another']]];
        $this->queueMockResponse(200, [], json_encode($response));

        $mockEpisode = $this->createMock(Episode::class);
        $recEng = $this->createMockRecEng();
        $result = $recEng->getRecommendations($mockEpisode, null, null, null, 2);

        $this->assertEquals([], $result);
    }

    public function testReturnsEmptyArrayOn404()
    {
        $this->queueMockResponse(404);

        $mockEpisode = $this->createMock(Episode::class);
        $recEng = $this->createMockRecEng();
        $result = $recEng->getRecommendations($mockEpisode, null, null, null, 2);

        $this->assertEquals([], $result);
    }

    private function createMockRecEng(): RecEngService
    {
        return new RecEngService(
            $this->client,
            'imASecretAudioKey',
            'imASecretVideoKey',
            $this->mockProgrammesService,
            $this->mockRouter,
            $this->mockLogger,
            $this->mockCache
        );
    }

    private function queueMockResponse($status, $headers = [], $body = "")
    {
        $response = new Response($status, $headers, $body);
        $this->mockHandler->append($response);
    }
}
