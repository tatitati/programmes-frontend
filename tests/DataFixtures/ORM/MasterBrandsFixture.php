<?php
declare(strict_types = 1);

namespace Tests\App\DataFixtures\ORM;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\MasterBrand;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Network;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class MasterBrandsFixture extends AbstractFixture implements DependentFixtureInterface
{
    /** @var ObjectManager $manager */
    private $manager;

    public function getDependencies()
    {
        return [
            NetworksAndServicesFixture::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $network = $this->getReference('network_bbc_radio_two');
        $this->buildMasterBrand('p1000001', 'p2000001', 'RADIOMASTER', $network);

        $network = $this->getReference('network_bbc_one');
        $this->buildMasterBrand('p1000002', 'p2000002', 'TVMASTER', $network);

        $this->manager->flush();
    }

    private function buildMasterBrand(
        string $mid,
        string $pid,
        string $name,
        Network $network
    ): MasterBrand {
        $entity = new MasterBrand($mid, $pid, $name);
        $entity->setNetwork($network);
        $entity->setStreamableInPlayspace(true);
        $this->manager->persist($entity);
        $this->addReference('masterbrand_' . $mid, $entity);
        return $entity;
    }
}
