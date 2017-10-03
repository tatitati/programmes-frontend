<?php
declare(strict_types = 1);

namespace App\Fixture\Guzzle;

use App\Fixture\ScenarioManagement\ScenarioManager;
use App\Fixture\ScenarioManagement\ScenarioState;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

class HttpFixtureMiddleware
{
    /** @var LoggerInterface */
    private $logger;

    /** @var ScenarioManager */
    private $scenarioManager;

    public function __construct(LoggerInterface $logger, ScenarioManager $scenarioManager)
    {
        $this->logger = $logger;
        $this->scenarioManager = $scenarioManager;
    }

    public function __invoke(callable $handler)
    {
        if (!$this->scenarioManager->scenarioIsActive()) {
            // Should never happen, but just return the normal response if it does
            return function (RequestInterface $request, array $options) use ($handler) {
                return $handler($request, $options);
            };
        }
        return function (RequestInterface $request, array $options) use ($handler) {
            $fixturedResponse = $this->scenarioManager->getHttpFixture($request);
            if ($fixturedResponse) {
                // For reading and regenerating scenarios we return a fixtured response
                // The scenario manager will blow up if the correct response is not found
                return new FulfilledPromise($fixturedResponse);
            }
            // Make the actual HTTP request and send the response to the scenariomanager
            /** @var Promise $promise */
            $promise = $handler($request, $options);
            /** @var Response $response */
            $response = $promise->wait(true);
            $this->scenarioManager->addHttpFixture($request, $response);
            return new FulfilledPromise($response);
        };
    }
}
