<?php
declare(strict_types=1);

namespace App\ExternalApi\Ada\Mapper;

use App\ExternalApi\Ada\Domain\AdaProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;

class AdaProgrammeMapper
{
    private $classMapper;

    public function __construct(AdaClassMapper $classMapper)
    {
        $this->classMapper = $classMapper;
    }

    public function mapItem(Programme $programme, $adaProgramme): AdaProgrammeItem
    {
        return new AdaProgrammeItem(
            $programme,
            $adaProgramme['pid'],
            $adaProgramme['title'],
            $adaProgramme['class_count'],
            $this->createAdaClassModels($adaProgramme)
        );
    }

    public function createAdaClassModels($itemData): ?array
    {
        $classModels = null;
        if (isset($itemData['via'])) {
            $classModels = [];
            foreach ($itemData['via'] as $viaClassData) {
                $classModels[] = $this->classMapper->mapItem($viaClassData, $itemData['pid']);
            }
        }
        return $classModels;
    }
}
