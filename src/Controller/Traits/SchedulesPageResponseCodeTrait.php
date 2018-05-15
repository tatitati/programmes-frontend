<?php
declare(strict_types = 1);

namespace App\Controller\Traits;

use App\ValueObject\BroadcastPeriod;

/**
 * This determines whether a week or daily schedule page should
 * a) 404
 * b) Add a no index tag
 * c) Render normally
 */
trait SchedulesPageResponseCodeTrait
{
    protected function setResponseCodeAndNoIndexProperties(
        bool $serviceIsActiveInThisPeriod,
        array $broadcasts,
        BroadcastPeriod $broadcastPeriod
    ): void {
        if (!$serviceIsActiveInThisPeriod) {
            // Dates where the service is not active are always 404s
            $this->response()->setStatusCode(404);
        } elseif (!$broadcasts && $broadcastPeriod->end()->isPast()) {
            // Dates in the past with no broadcasts are not 404
            // because it's too expensive to determine whether to link to them or not.
            // Instead they are 200 with a noindex meta tag
            $this->metaNoIndex = true;
        } elseif (!$broadcasts && !($broadcastPeriod->start()->isToday() || $broadcastPeriod->start()->isWithinNext('+35 days'))) {
            // Dates more than 35 days in the future without broadcasts are 404s
            $this->response()->setStatusCode(404);
        }
    }
}
