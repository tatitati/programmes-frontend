<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Mapper;

use App\ExternalApi\Isite\Domain\ContentBlock;
use SimpleXMLElement;

class ContentBlockMapper extends Mapper
{
    public function getDomainModel(SimpleXMLElement $isiteObject)
    {
        $metadata = $this->getMetaData($isiteObject);

        return new ContentBlock($this->getString($metadata->type));
    }
}
