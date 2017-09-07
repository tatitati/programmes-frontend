<?php
declare(strict_types=1);

namespace Tests\App\DataFixtures\ORM\EntityCreationHelper;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Brand;

class BrandCreationHelper
{
    /** @var int */
    private $count = 0;

    /**
     * @param int $amount
     * @param string $prefix
     * @return Brand[]
     */
    public function create(int $amount, string $prefix = 'prstdbrnd'): array
    {
        $entities = [];

        for ($i = 0; $i < $amount; $i++) {
            $this->count += 1;
            $id = $prefix . $this->count;
            $entities[$id] = new Brand($id, 'Brand ' . $this->count);
        }

        return $entities;
    }
}
