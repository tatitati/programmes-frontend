<?php
declare(strict_types = 1);

namespace App\Metrics\Backend;

use App\Metrics\ProgrammesMetrics\ProgrammesMetricInterface;

interface MetricBackendInterface
{
    /**
     * @param ProgrammesMetricInterface[] $metrics
     */
    public function sendMetrics(array $metrics): void;
}
