<?php

namespace App\Metrics;

use App\Fixture\Doctrine\EntityRepository\HttpFixtureRepository;
use App\Fixture\ScenarioManager;
use BBC\BrandingClient\OrbitClient;
use GuzzleHttp\Promise\FulfilledPromise;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\TransferStats;
use RuntimeException;

class MetricsMiddleware
{
    /** @var MetricsManager */
    private $metricsManager;

    /** @var LoggerInterface */
    private $logger;

    private $apiDomainToApiName = [
        MetricsManager::API_ORBIT => '/^navigation\.(int\.|test\.|stage\.)?api\.bbci\.co\.uk/i',
        MetricsManager::API_BRANDING => '/^branding\.(int\.|test\.|stage\.)?files\.bbci\.co\.uk/i',
    ];

    public function __construct(MetricsManager $metricsManager, LoggerInterface $logger)
    {
        $this->metricsManager = $metricsManager;
        $this->logger = $logger;
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $this->logger->info("HTTP Request:" . $request->getUri());

            $options['on_stats'] = function (TransferStats $stats) use (&$responseTime) {
                $uri = $stats->getEffectiveUri();
                $apiName = $this->getApiNameFromUrl($uri);
                if (!$apiName) {
                    return;
                }
                $responseTime = (int) round($stats->getTransferTime() * 1000);
                // No response/timeout is logged as a 504 (gateway timeout). Which isn't correct. But whatever.
                $responseCode = 504;
                if ($stats->hasResponse()) {
                    $responseCode = (int) $stats->getResponse()->getStatusCode();
                }
                $this->metricsManager->addApiMetric($apiName, $responseTime, $responseCode);
            };

            return $handler($request, $options);
        };
    }

    private function getApiNameFromUrl(UriInterface $uri) : ?string
    {
        $host = $uri->getHost();

        foreach ($this->apiDomainToApiName as $apiName => $apiPattern) {
            if (preg_match($apiPattern, $host)) {
                return $apiName;
            }
        }

        return null;
    }
}
