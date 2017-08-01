<?php
declare(strict_types = 1);

namespace Test\App\Metrics\ProgrammesMetrics;

use App\Metrics\ProgrammesMetrics\RouteMetric;
use PHPUnit\Framework\TestCase;

/**
 * @group metrics
 */
class RouteMetricTest extends TestCase
{
    public function testMetricProvideCorrectDataStructureForAWS()
    {
        $metric = new RouteMetric('SchedulesByDayController', 1233);
        $this->assertEquals([
                [
                    'MetricName' => 'route_time',
                    'Dimensions' => [
                        [
                            'Name' => 'controller',
                            'Value' => 'SchedulesByDayController',
                        ],
                    ],
                    'Value' => 1233,
                    'Unit' => 'Milliseconds',
                ],
                [
                    'MetricName' => 'route_count',
                    'Dimensions' => [
                        [
                            'Name' => 'controller',
                            'Value' => 'SchedulesByDayController',
                        ],
                    ],
                    'Value' => 1,
                    'Unit' => 'Count',
                ],
            ], $metric->getMetricData());
    }

    public function testMetricCreateCorrectKeysToCache()
    {
        $metric = new RouteMetric('SchedulesByDayController', 1233, 666);

        $this->assertEquals(
            [
                'route#SchedulesByDayController#count' => 666,
                'route#SchedulesByDayController#time' => 1233,
            ],
            $metric->getCacheKeyValuePairs()
        );
    }

    public function testWeSendAvgTimeToAws()
    {
        $timeMs = 1000;
        $count = 10;
        $metric = new RouteMetric('SchedulesByDayController', $timeMs, $count);

        $this->assertEquals(
            $timeMs/$count,
            $metric->getMetricData()[0]['Value']
        );
    }

    public function testMetricCanSetCountFromCachedKey()
    {
        $timeMs = 7777;
        $count = 12;

        $metric = new RouteMetric('SchedulesByDayController', 10);
        $metric->setValuesFromCacheKeyValuePairs(
            [
                'route#SchedulesByDayController#time' => $timeMs,
                'route#SchedulesByDayController#count' => $count,
            ]
        );

        $this->assertEquals(
            [
                [
                    'MetricName' => 'route_time',
                    'Dimensions' => [
                        [
                            'Name' => 'controller',
                            'Value' => 'SchedulesByDayController',
                        ],
                    ],
                    'Value' => $timeMs/$count,
                    'Unit' => 'Milliseconds',
                ],
                [
                    'MetricName' => 'route_count',
                    'Dimensions' => [
                        [
                            'Name' => 'controller',
                            'Value' => 'SchedulesByDayController',
                        ],
                    ],
                    'Value' => $count,
                    'Unit' => 'Count',
                ],
            ],
            $metric->getMetricData()
        );
    }
}
