<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Tupac\Service;

use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\Tupac\Domain\Record;
use App\ExternalApi\Tupac\Service\TupacService;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use ReflectionClass;
use Tests\App\ExternalApi\BaseServiceTestCase;

/**
 * @group podcast
 */
class TupacServiceTest extends BaseServiceTestCase
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
        $historyRequests = [];
        $this->givenHistoryOfRequests($historyRequests); // $historyRequests is passed by Reference
        $service = $this->service();

        //- 1. Check TupacService build the right API request
        $recordsIdsArray = ['nz4zv2', 'n4mz26', 'n5zdgc', 'nznmbb'];
        $service->fetchRecordsByIds($recordsIdsArray, true)->wait(true);
        $this->assertCount(1, $historyRequests);
        $this->assertEquals(
            'https://music-tupac.api.bbc.co.uk/music/v2/records?context=programmes&resultsPerPage=100&uk=1&id=nz4zv2&id=n4mz26&id=n5zdgc&id=nznmbb',
            (string) $historyRequests[0]['request']->getUri()
        );

        //- 2. Check "uk=0" in the URL if the parameter is not set
        $service->fetchRecordsByIds($recordsIdsArray)->wait(true);
        $this->assertCount(2, $historyRequests);
        $this->assertEquals(
            'https://music-tupac.api.bbc.co.uk/music/v2/records?context=programmes&resultsPerPage=100&uk=0&id=nz4zv2&id=n4mz26&id=n5zdgc&id=nznmbb',
            (string) $historyRequests[1]['request']->getUri()
        );

        //- 3. Test only one record
        $recordsIdsArray = ['n4mz26'];
        $service->fetchRecordsByIds($recordsIdsArray)->wait(true);
        $this->assertCount(3, $historyRequests);
        $this->assertEquals(
            'https://music-tupac.api.bbc.co.uk/music/v2/records?context=programmes&resultsPerPage=100&uk=0&id=n4mz26',
            (string) $historyRequests[2]['request']->getUri()
        );

        //- 4. Test empty $recordsIdsArray doesn't do a request
        $recordsIdsArray = [];
        $service->fetchRecordsByIds($recordsIdsArray)->wait(true);
        $this->assertCount(3, $historyRequests);
    }

    public function testApiResponseMap()
    {
        $historyRequests = [];
        $this->givenHistoryOfRequests($historyRequests);
        $service = $this->service();

        // API response for https://music-tupac.api.bbc.co.uk/music/v2/records?context=programmes&resultsPerPage=100&uk=0&id=nz4zv2,&id=n4mz26
        $jsonResponse200 = file_get_contents(dirname(dirname(__DIR__)) . '/Tupac/200_response_nz4zv2_n4mz26.json');
        $responseArray = [new Response(200, [], $jsonResponse200)];
        $response = $this->invokePrivateMethod($service, 'parseResponse', [$responseArray]);
        $this->assertEquals($this->getExpectedResponseMapped(), $response);
    }

    /**
     * This is what the API response to "https://music-tupac.api.bbc.co.uk/music/v2/records?context=programmes&resultsPerPage=100&uk=0&id=nz4zv2,&id=n4mz26"
     * should be mapped to
     * @return array
     */
    private function getExpectedResponseMapped()
    {
        $records[] = new Record(
            'n4mz26',
            'Spit/Swallow',
            'Skinny Pelembe',
            'b6a4f1d2-6d29-4452-b63a-b52c4eff4afc',
            'p061r5ng',
            null,
            'https://music-audio.files.bbci.co.uk/n4mz26-pPoxIA.mp3',
            'mp3',
            'snippet'
        );
        $records[] = new Record(
            'nz4zv2',
            'Give It Up',
            'Public Enemy',
            'bf2e15d0-4b77-469e-bfb4-f8414415baca',
            'p01rrm7n',
            30000,
            'https://music-audio.files.bbci.co.uk/nz4zv2-rml0La.mp3',
            'mp3',
            'snippet'
        );

        return $records;
    }

    private function givenHistoryOfRequests(&$recordedRequests)
    {
        $history = Middleware::history($recordedRequests);
        $stack = HandlerStack::create();
        $stack->push($history);

        $this->client = new Client(['handler' => $stack]);
    }

    /**
     * Helpers
     */
    private function service(): TupacService
    {
        return new TupacService(
            new HttpApiClientFactory(
                $this->client,
                $this->cache,
                $this->logger
            ),
            'https://music-tupac.api.bbc.co.uk'
        );
    }

    private function invokePrivateMethod($object, string $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
