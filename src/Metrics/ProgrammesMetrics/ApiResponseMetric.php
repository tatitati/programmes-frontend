<?php
declare(strict_types = 1);

namespace App\Metrics\ProgrammesMetrics;

class ApiResponseMetric implements ProgrammesMetricInterface
{
    /** @var string */
    private $apiName;

    /** @var int */
    private $count;

    /** @var string */
    private $responseType;

    public function __construct(string $apiName, string $responseType, int $count = 1)
    {
        $this->apiName = $apiName;
        $this->count = $count;
        $this->responseType = $responseType;
    }

    /**
     * This function returns the cache keys to set and the
     * values to set them to.
     */
    public function getCacheKeyValuePairs(): array
    {
        return [
            $this->cacheKey('count') => $this->count,
        ];
    }

    /**
     * Accepts the format returned by getCacheKeyValuePairs, but sets
     * the values in the class from values returned from the cache.
     */
    public function setValuesFromCacheKeyValuePairs(array $keyValuePairs): void
    {
        $this->count = $keyValuePairs[$this->cacheKey()];
    }

    public function getMetricData(): array
    {
        $dimensions = [
            [ 'Name' => 'api', 'Value' => $this->apiName ],
            [ 'Name' => 'response_type', 'Value' => $this->responseType],
        ];
        return [
            [
                'MetricName' => 'api_count',
                'Dimensions' => $dimensions,
                'Value' => $this->count,
                'Unit' => 'Count',
            ],
        ];
    }

    private function cacheKey()
    {
        return implode('#', ['api_response', $this->apiName, $this->responseType]);
    }
}
