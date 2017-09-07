<?php
declare(strict_types=1);

namespace Tests\App\DataFixtures\ORM\EntityCreationHelper;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Clip;

class ClipCreationHelper
{
    /** @var int */
    private $count = 0;

    /**
     * @param int $amount
     * @param string $prefix
     * @return Clip[]
     */
    public function create(int $amount, string $prefix = 'prstdclp'): array
    {
        $entities = [];

        for ($i = 0; $i < $amount; $i++) {
            $this->count += 1;
            $id = $prefix . $this->count;
            $entities[$id] = new Clip($id, 'Clip ' . $this->count);
        }

        return $entities;
    }
}
