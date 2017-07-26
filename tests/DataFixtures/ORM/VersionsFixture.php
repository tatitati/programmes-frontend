<?php
declare(strict_types = 1);
namespace Tests\App\DataFixtures\ORM;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Version;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class VersionsFixture extends AbstractFixture implements DependentFixtureInterface
{
    /** @var ObjectManager $manager */
    private $manager;

    public function getDependencies()
    {
        return [
            ProgrammeItemsFixture::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $episode = $this->getReference('p3000001');
        $this->buildVersion('p4000001', $episode);

        $episode = $this->getReference('p3000002');
        $this->buildVersion('p4000002', $episode);

        $episode = $this->getReference('p3000003');
        $this->buildVersion('p4000003', $episode);

        $episode = $this->getReference('p3000004');
        $this->buildVersion('p4000004', $episode);

        $this->manager->flush();
    }

    private function buildVersion(
        string $pid,
        ProgrammeItem $programmeItem
    ) {
        $entity = new Version($pid, $programmeItem);
        $this->manager->persist($entity);
        $this->addReference($pid, $entity);
        return $entity;
    }
}
