<?php
declare(strict_types = 1);

namespace App\ValueObject;

use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use RMP\Comscore\Comscore;

/**
 * Class ComscoreAnalyticsLabels
 *
 * Integration with BBC Worldwide "Comscore" analytics package via rmp/comscore composer package
 *
 * @package App\ValueObject
 */
class ComscoreAnalyticsLabels
{
    /** @var Comscore|null */
    private $comscore;

    public function __construct($context, CosmosInfo $cosmosInfo, IstatsAnalyticsLabels $istatsAnalyticsLabels, string $canonicalUrl)
    {
        $network = null;
        if ($context instanceof CoreEntity || $context instanceof Service) {
            $network = $context->getNetwork();
        }
        if ($network && $network->isInternational()) {
            $this->setComscore($cosmosInfo, $istatsAnalyticsLabels, $canonicalUrl);
        }
    }

    public function getComscore(): ?Comscore
    {
        return $this->comscore;
    }

    private function setComscore(CosmosInfo $cosmosInfo, IstatsAnalyticsLabels $istatsAnalyticsLabels, string $canonicalUrl)
    {
        $labels = $istatsAnalyticsLabels->getLabels();
        $data = [
            'env' => $cosmosInfo->getAppEnvironment(),
            'bbc_site' => $labels['bbc_site'] ?? 'unknown',
            'page_url' => $canonicalUrl,
            'page_title' => $labels['programme_title'] ?? '',
            'character_encoding' => 'utf-8',
            'master_brand' => $labels['event_master_brand'] ?? null,
            'app_name' => 'programmes',
            'app_version' => $cosmosInfo->getAppVersion(),
            // Programmes specific labels
            'page_type' => $labels['progs_page_type'] ?? null,
            'brand_id' => $labels['brand_id'] ?? null,
            'brand_title' => $labels['brand_title'] ?? null,
            'series_id' => $labels['series_id'] ?? null,
            'series_title' => $labels['series_title'] ?? null,
            'programme_title' => $labels['programme_title'] ?? null,
        ];
        $this->comscore = new Comscore('WorldService', $data);
    }
}
