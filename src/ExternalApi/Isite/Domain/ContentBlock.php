<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Domain;

class ContentBlock //@TODO Change to abstract
{
    /** @var string */
    private $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
