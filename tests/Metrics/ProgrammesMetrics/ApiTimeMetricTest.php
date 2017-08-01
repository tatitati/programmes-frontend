<?php
declare(strict_types = 1);

namespace Tests\App\Metrics\ProgrammesMetrics;

use App\Metrics\ProgrammesMetrics\ApiTimeMetric;
use PHPUnit\Framework\TestCase;

/**
 * @group metrics
 */
class ApiTimeMetricTest extends TestCase
{
    public function testMetricProvideCorrectDataStructureForAWS()
    {
        $metric = new ApiTimeMetric('BRANDING', 1232);
        $this->assertEquals([
                [
                    'MetricName' => 'api_time',
                    'Dimensions' => [
                        [
                            'Name' => 'api',
                            'Value' => 'BRANDING',
                        ],
                        [
                            'Name' => 'response_type',
                            'Value' => 'All',
                        ],
                    ],
                    'Value' => 1232,
                    'Unit' => 'Milliseconds',
                ],
                [
                    'MetricName' => 'api_count',
                    'Dimensions' => [
                        [
                            'Name' => 'api',
                            'Value' => 'BRANDING',
                        ],
                        [
                            'Name' => 'response_type',
                            'Value' => 'All',
                        ],
                    ],
                    'Value' => 1,
                    'Unit' => 'Count',
                ],
            ], $metric->getMetricData());
    }

    public function testMetricCreateCorrectKeysToCache()
    {
        $metric = new ApiTimeMetric('BRANDING', 1232, 77);
        $this->assertEquals(
            [
                'api_time#BRANDING#count' => 77,
                'api_time#BRANDING#time' => 1232,
            ],
            $metric->getCacheKeyValuePairs()
        );
    }

    public function testMetricCanSetCountFromCachedKey()
    {
        $count = 88;
        $timeMs = 6666;

        $metric = new ApiTimeMetric('BRANDING', 1232, 77);
        $metric->setValuesFromCacheKeyValuePairs(
            [
                'api_time#BRANDING#count' => $count,
                'api_time#BRANDING#time' => $timeMs,
            ]
        );

        $this->assertEquals([
                [
                    'MetricName' => 'api_time',
                    'Dimensions' => [
                        [
                            'Name' => 'api',
                            'Value' => 'BRANDING',
                        ],
                        [
                            'Name' => 'response_type',
                            'Value' => 'All',
                        ],
                    ],
                    'Value' => $timeMs/$count,
                    'Unit' => 'Milliseconds',
                ],
                [
                    'MetricName' => 'api_count',
                    'Dimensions' => [
                        [
                            'Name' => 'api',
                            'Value' => 'BRANDING',
                        ],
                        [
                            'Name' => 'response_type',
                            'Value' => 'All',
                        ],
                    ],
                    'Value' => $count,
                    'Unit' => 'Count',
                ],
            ], $metric->getMetricData());
    }
}
