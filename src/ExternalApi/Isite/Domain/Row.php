<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Domain;

use App\ExternalApi\Isite\Domain\ContentBlock\AbstractContentBlock;

class Row
{
    /** @var AbstractContentBlock[] */
    private $primary;

    /** @var AbstractContentBlock[] */
    private $secondary;

    public function __construct(array $primary, array $secondary)
    {
        $this->primary = $primary;
        $this->secondary = $secondary;
    }

    public function getPrimaryBlocks(): array
    {
        return $this->primary;
    }

    public function getSecondaryBlocks(): array
    {
        return $this->secondary;
    }
}
