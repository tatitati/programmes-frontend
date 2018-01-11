<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\RecEng\Service;

use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\RecEng\Service\RecEngService;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use GuzzleHttp\Psr7\Response;
use PHPUnit_Framework_MockObject_MockObject;
use Tests\App\ExternalApi\BaseServiceTestCase;

/**
 * @covers \App\ExternalApi\RecEng\Service\RecEngService
 */
class RecEngServiceTest extends BaseServiceTestCase
{
    /** @var ProgrammesService|PHPUnit_Framework_MockObject_MockObject */
    private $mockProgrammesService;

    public function setUp()
    {
        $this->setUpCache();
        $this->setUpLogger();

        $this->mockProgrammesService = $this->createMock(ProgrammesService::class);
    }

    public function testParsesRecEngResponseCorrectly()
    {
        $response = ['recommendations' => [['ref' => 'urn:bbc:pips:p04vjd23'], ['ref' => 'urn:bbc:pips:p0576pm5']]];
        $service = $this->service(
            $this->client([new Response(200, [], json_encode($response))])
        );

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

        $result = $service->getRecommendations($mockEpisode, null, null, null, 2);

        $this->assertContainsOnlyInstancesOf(Programme::class, $result);
        $this->assertContains('p04vjd23', [$result[0]->getPid(), $result[1]->getPid()]);
        $this->assertContains('p0576pm5', [$result[0]->getPid(), $result[1]->getPid()]);
    }

    /** @dataProvider isVideoOrAudioProvider */
    public function testUsesCorrectRecEngApiKey(bool $isVideo, string $expectedKey)
    {
        $history = [];
        $response = ['recommendations' => [['ref' => 'urn:bbc:pips:p04vjd23'], ['ref' => 'urn:bbc:pips:p0576pm5']]];
        $service = $this->service(
            $this->client([new Response(200, [], json_encode($response))], $history)
        );

        $mockEpisode = $this->createMock(Episode::class);
        $mockEpisode->method('isVideo')->willReturn($isVideo);

        $this->mockProgrammesService->method('findByPids')->willReturn([$this->createMock(Programme::class)]);

        $result = $service->getRecommendations($mockEpisode, null, null, null, 2);

        $requestQueryString = $this->getLastRequestUrl($history);
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
        $service = $this->service(
            $this->client([new Response(200, [], json_encode($response))])
        );

        $mockEpisode = $this->createMock(Episode::class);

        $mockRecProgrammeOne = $this->createMock(Programme::class);
        $mockRecProgrammeOne->method('getPid')->willReturn(new Pid('p04vjd23'));

        $programmesServiceResult = [$mockRecProgrammeOne];
        $this->mockProgrammesService
            ->expects($this->once())
            ->method('findByPids')
            ->with([new Pid('p04vjd23')])
            ->willReturn($programmesServiceResult);

        $result = $service->getRecommendations($mockEpisode, null, null, null, 1);

        $this->assertContainsOnlyInstancesOf(Programme::class, $result);
        $this->assertContains('p04vjd23', [$result[0]->getPid()]);
    }

    public function testReturnsEmptyArrayForResponseWithNoRecommendations()
    {
        $service = $this->service(
            $this->client([new Response(200, [], '{"recommendations":[]}')])
        );

        $mockEpisode = $this->createMock(Episode::class);
        $this->mockProgrammesService->method('findByPids')->willReturn([]);

        $result = $service->getRecommendations($mockEpisode, null, null, null, 2);

        $this->assertEquals([], $result);
    }

    public function testReturnsEmptyArrayForEmptyResponse()
    {
        $service = $this->service(
            $this->client([new Response(200, [])])
        );

        $mockEpisode = $this->createMock(Episode::class);
        $this->mockProgrammesService->method('findByPids')->willReturn([]);

        $result = $service->getRecommendations($mockEpisode, null, null, null, 2);

        $this->assertEquals([], $result);
    }

    public function testReturnsEmptyArrayForInvalidPids()
    {
        $response = ['recommendations' => [['ref' => 'urn:bbc:pips:invalidpid'], ['ref' => 'urn:bbc:pips:another']]];
        $service = $this->service(
            $this->client([new Response(200, [], json_encode($response))])
        );

        $mockEpisode = $this->createMock(Episode::class);
        $result = $service->getRecommendations($mockEpisode, null, null, null, 2);

        $this->assertEquals([], $result);
    }

    public function testReturnsEmptyArrayOn404()
    {
        $service = $this->service(
            $this->client([new Response(404)])
        );

        $mockEpisode = $this->createMock(Episode::class);
        $result = $service->getRecommendations($mockEpisode, null, null, null, 2);

        $this->assertEquals([], $result);
    }

    private function service($client): RecEngService
    {
        return new RecEngService(
            new HttpApiClientFactory($client, $this->cache, $this->logger),
            'imASecretAudioKey',
            'imASecretVideoKey',
            $this->mockProgrammesService,
            'https://api.example.com/'
        );
    }
}
