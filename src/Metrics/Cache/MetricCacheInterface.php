<?php
declare(strict_types = 1);

namespace App\Metrics\Cache;

use App\Metrics\ProgrammesMetrics\ProgrammesMetricInterface;

interface MetricCacheInterface
{
    /**
     * @param ProgrammesMetricInterface[] $metrics
     */
    public function cacheMetrics(array $metrics): void;

    /**
     * @param callable $getAllMetrics - This function must return a list of all possible ProgrammesMetricInterface
     * @return ProgrammesMetricInterface[]
     */
    public function getAndClearReadyToSendMetrics(callable $getAllMetrics): array;
}
