<?php
declare(strict_types=1);

namespace Tests\App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

abstract class BaseORMFixture extends AbstractFixture
{
    protected function persistEntities(ObjectManager $manager, array $entities): void
    {
        foreach ($entities as $reference => $entity) {
            $manager->persist($entity);
            $this->addReference($reference, $entity);
        }

        $manager->flush();
    }
}
