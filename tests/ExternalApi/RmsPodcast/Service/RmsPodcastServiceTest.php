<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\RmsPodcast\Service;

use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\RmsPodcast\Domain\RmsPodcast;
use App\ExternalApi\RmsPodcast\Mapper\RmsPodcastMapper;
use App\ExternalApi\RmsPodcast\Service\RmsPodcastService;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Tests\App\ExternalApi\BaseServiceTestCase;

/**
 * @group podcast
 */
class RmsPodcastServiceTest extends BaseServiceTestCase
{
    /** @var Client */
    private $client;

    public function setUp()
    {
        $this->setUpCache();
        $this->setUpLogger();
    }

    public function testServiceMakesTheProperRequests()
    {
        $pid = new Pid('b0000001');

        $historyRequests = [];
        $this->givenHistoryOfRequests($historyRequests);

        $service = $this->service();
        $service->getPodcast($pid)->wait(true);
        $service->getPodcast($pid)->wait(true);

        $this->assertCount(2, $historyRequests);
        $this->thenRequestAppearInTheHistory('http://podcast-url-api.com/something/b0000001.json', $historyRequests);
    }

    public function testServiceCanFetchPodcastsFromService()
    {
        $dummy = $this->createMock(Pid::class);

        $this->givenServerRespondsWith(200);

        $result = $this->service()->getPodcast($dummy)->wait(true);

        $this->assertInstanceOf(RmsPodcast::class, $result);
    }

    public function testCannotFetchPodcastFromService()
    {
        $dummy = $this->createMock(Pid::class);

        $this->givenServerRespondsWith(404);

        $result = $this->service()->getPodcast($dummy)->wait(true);

        $this->assertNull($result);
    }

    public function testServerConnection()
    {
        $dummy = $this->createMock(Pid::class);

        $this->givenServerRespondsWith('Exception');

        $result = $this->service()->getPodcast($dummy)->wait(true);

        $this->assertNull($result);
    }

    /**
     * Helpers
     */
    private function service(): RmsPodcastService
    {
        return new RmsPodcastService(
            'http://podcast-url-api.com/something',
            'api-key',
            new HttpApiClientFactory(
                $this->client,
                $this->cache,
                $this->logger
            ),
            new RmsPodcastMapper()
        );
    }

    private function givenServerRespondsWith($responseCode = '')
    {
        $jsonResponse200 = file_get_contents(dirname(dirname(__DIR__)) . '/RmsPodcast/200_response_b006qykl.json');

        $response200 = new Response(200, [], $jsonResponse200);
        $response404 = new Response(404, [], 'Not found');
        $responseException = new RequestException("Error Communicating with Server", new Request('GET', 'test'));

        switch ($responseCode) {
            case 200:
                $response = $response200;
                break;
            case 404:
                $response = $response404;
                break;
            default:
                $response = $responseException;
                break;
        }

        $stackResponsesServer = new MockHandler([$response]);

        $handler = HandlerStack::create($stackResponsesServer);
        $this->client = new Client(['handler' => $handler]);
    }

    private function givenHistoryOfRequests(&$recordedRequests)
    {
        $history = Middleware::history($recordedRequests);
        $stack = HandlerStack::create();
        $stack->push($history);

        $this->client = new Client(['handler' => $stack]);
    }

    private function thenRequestAppearInTheHistory(string $url, array $historyRequests)
    {
        $request1 = $historyRequests[0]['request'];
        $request2 = $historyRequests[1]['request'];

        $this->assertSame($url, (string) $request1->getUri());
        $this->assertSame($url, (string) $request2->getUri());
    }
}
