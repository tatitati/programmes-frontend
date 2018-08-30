<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Mapper;

use App\ExternalApi\Isite\Domain\Row;
use Exception;
use SimpleXMLElement;

class RowMapper extends Mapper
{
    public function getDomainModel(SimpleXMLElement $isiteObject): Row
    {
        return new Row(
            $this->extractBlocks($isiteObject, 'primary'),
            $this->extractBlocks($isiteObject, 'secondary')
        );
    }

    private function extractBlocks(SimpleXMLElement $isiteObject, string $type): array
    {
        //check if module is in the data
        if (empty($isiteObject->{$type})) {
            return [];
        }

        $blocks = $isiteObject->{$type};
        $name = $type . '-blocks';

        if (empty($blocks[0]->{$name})) {
            return [];
        }

        if (empty($blocks[0]->{$name}->result)) {
            throw new Exception('Blocks have not been fetched');
        }
        $contentBlocks = [];
        foreach ($blocks as $block) {
            if ($this->isPublished($block->{$name})) { // Must be published
                $contentBlocks[] = $this->mapperFactory->createContentBlockMapper()->getDomainModel(
                    $block->{$name}->result
                );
            }
        }

        return $contentBlocks;
    }
}
