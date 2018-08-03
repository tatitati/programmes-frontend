<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Mapper;

use App\ExternalApi\Isite\Domain\KeyFact;
use SimpleXMLElement;

class KeyFactMapper extends Mapper
{
    public function getDomainModel(SimpleXMLElement $isiteObject)
    {
        // @codingStandardsIgnoreStart
        // Ignored PHPCS cause of snake variable fields included in the xml
        return new KeyFact($this->getString($isiteObject->key_fact_title), $this->getString($isiteObject->key_fact_answer), $this->getString($isiteObject->key_fact_url));
        // @codingStandardsIgnoreEnd
    }
}
