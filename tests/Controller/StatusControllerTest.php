<?php
declare(strict_types = 1);
namespace Tests\App\Controller;

use BBC\ProgrammesPagesService\Service\BroadcastsService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use BBC\ProgrammesPagesService\Service\SegmentEventsService;
use BBC\ProgrammesPagesService\Service\VersionsService;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Tests\App\BaseWebTestCase;

/**
 * @covers App\Controller\StatusController
 */
class StatusControllerTest extends BaseWebTestCase
{
    public function testStatus()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/status');
        $this->assertResponseStatusCode($client, 200);

        $this->assertEquals('YES', $crawler->filter('[data-test-name=db-connectivity] span')->text());
        $this->assertHasRequiredResponseHeaders($client, 'no-cache, private');
    }

    public function testStatusFromElb()
    {
        $client = static::createClient([], [
            'HTTP_USER_AGENT' => 'ELB-HealthChecker/1.0',
        ]);
        $crawler = $client->request('GET', '/status');

        $this->assertResponseStatusCode($client, 200);
        $this->assertEquals('OK', $client->getResponse()->getContent());
        $this->assertHasRequiredResponseHeaders($client, 'no-cache, private');
    }

    /**
     * @group legacy
     * @expectedDeprecation Setting the "%s" private service is deprecated since Symfony 3.2 and won't be supported anymore in Symfony 4.0
     */
    public function testNonConnectionDBErrorFromElb()
    {
        $client = static::createClient([], [
            'HTTP_USER_AGENT' => 'ELB-HealthChecker/1.0',
        ]);

        // clip mock
        $mockProgrammeService = $this->createMock(ProgrammesService::class);
        $mockProgrammeService->expects($this->once())->method('findByPidFull');
        $client->getContainer()->set(ProgrammesService::class, $mockProgrammeService);

        // broadcast service mock
        $mockBroadcastService = $this->createMock(BroadcastsService::class);
        $mockBroadcastService->expects($this->once())->method('findByServiceAndDateRange');
        $client->getContainer()->set(BroadcastsService::class, $mockBroadcastService);

        // version mock
        $mockVersionService = $this->createMock(VersionsService::class);
        $mockVersionService->expects($this->once())->method('findByPidFull');
        $client->getContainer()->set(VersionsService::class, $mockVersionService);

        // segment events mock. This one throw an exception and injects it into the container
        $mockSegmentEventsService = $this->createMock(SegmentEventsService::class);
        $mockSegmentEventsService->expects($this->once())
            ->method('findByPidFull')
            ->willThrowException(new DBALException("Something bad happened."));
        $client->getContainer()->set(SegmentEventsService::class, $mockSegmentEventsService);

        $client->request('GET', '/status');

        $this->assertResponseStatusCode($client, 500);
        $this->assertEquals('ERROR', $client->getResponse()->getContent());
    }

    /**
     * @group legacy
     * @expectedDeprecation Setting the "%s" private service is deprecated since Symfony 3.2 and won't be supported anymore in Symfony 4.0
     */
    public function testConnectionDBErrorFromElb()
    {
        $client = static::createClient([], [
            'HTTP_USER_AGENT' => 'ELB-HealthChecker/1.0',
        ]);

        // clip mock
        $mockProgrammeService = $this->createMock(ProgrammesService::class);
        $mockProgrammeService->expects($this->once())->method('findByPidFull');
        $client->getContainer()->set(ProgrammesService::class, $mockProgrammeService);

        // broadcast service mock
        $mockBroadcastService = $this->createMock(BroadcastsService::class);
        $mockBroadcastService->expects($this->once())->method('findByServiceAndDateRange');
        $client->getContainer()->set(BroadcastsService::class, $mockBroadcastService);

        // version mock
        $mockVersionService = $this->createMock(VersionsService::class);
        $mockVersionService->expects($this->once())->method('findByPidFull');
        $client->getContainer()->set(VersionsService::class, $mockVersionService);

        // segment events mock. This one throw an exception and injects it into the container
        $mockSegmentEventsService = $this->createMock(SegmentEventsService::class);
        $mockSegmentEventsService->expects($this->once())
            ->method('findByPidFull')
            ->willThrowException(new ConnectionException("Cannot Connect."));
        $client->getContainer()->set(SegmentEventsService::class, $mockSegmentEventsService);

        $client->request('GET', '/status');

        $this->assertResponseStatusCode($client, 200);
        $this->assertEquals('OK', $client->getResponse()->getContent());
    }
}
