<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class HttpApiTestBase extends TestCase
{
    protected function makeGuzzleClientToRespondWith(Response $response): Client
    {
        $mockHandler = new MockHandler();
        $container = [];
        $stack = HandlerStack::create($mockHandler);
        $history = Middleware::history($container);
        $stack->push($history);

        $client = new Client(['handler' => $stack]);
        $mockHandler->append($response);
        return $client;
    }
}
