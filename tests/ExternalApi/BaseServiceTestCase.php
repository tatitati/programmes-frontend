<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi;

use BBC\ProgrammesPagesService\Cache\Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Middleware;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

abstract class BaseServiceTestCase extends TestCase
{
    protected $cache;

    private $cacheAdapter;

    protected $logger;

    private $loggerHandler;

    protected function setUpCache()
    {
        $this->cacheAdapter = new ArrayAdapter(0, false);
        $this->cache = new Cache($this->cacheAdapter, 'test');
    }

    protected function setUpLogger()
    {
        $this->loggerHandler = new TestHandler();

        $this->logger = new Logger(
            'test',
            [$this->loggerHandler],
            [new PsrLogMessageProcessor()]
        );
    }

    protected function client(array $mockResponses = [], &$historyContainer = null): Client
    {
        // Mock Requests
        $handler = MockHandler::createWithMiddleware($mockResponses);
        // History
        if (!is_null($historyContainer)) {
            $handler->push(Middleware::history($historyContainer));
        }
        return new Client(['handler' => $handler]);
    }

    protected function getLoggerHandler()
    {
        return $this->loggerHandler;
    }

    protected function getLastRequestUrl($history)
    {
        return (string) $history[0]['request']->getUri();
    }


    protected function validCacheValues()
    {
        // In the ArrayAdaptor if you call getItem() on a non-existant value it
        // will try and store null into the values array immediatly, however we
        // only want values that are not null
        return array_filter($this->cacheAdapter->getValues(), function ($v) {
            return $v !== null;
        });
    }
}
