<?php
declare(strict_types = 1);
namespace Tests\App\DataFixtures\ORM;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Network;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Service;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

class NetworksFixture extends AbstractFixture implements DependentFixtureInterface
{
    /** @var ObjectManager $manager */
    private $manager;

    public function getDependencies()
    {
        return [
            ServicesFixture::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        // TV
        $tvService = $this->getReference('p00fzl6p');
        $this->buildNetwork('bbc_one', 'BBC One', $tvService, 'TV');

        // National Radio
        $nationalService = $this->getReference('p00fzl8v');
        $this->buildNetwork('bbc_radio_two', 'BBC Radio 2', $nationalService, 'National Radio');

        // Regional Radio
        $regionalService = $this->getReference('p00fzl7b');
        $this->buildNetwork('bbc_radio_cymru', 'BBC Radio Cymru', $regionalService, 'Regional Radio');

        // Local Radio
        $localService = $this->getReference('p00fzl74');
        $this->buildNetwork('bbc_radio_four', 'BBC Radio Berkshire', $localService, 'Local Radio');

        $this->manager->flush();
    }

    private function buildNetwork(
        string $nid,
        string $title,
        Service $defaultService,
        string $type
    ): Network {
        $entity = new Network($nid, $title, $title);
        $entity->setDefaultService($defaultService);
        $defaultService->setNetwork($entity);
        $this->manager->persist($defaultService);
        $entity->setPosition(1);
        $entity->setType($type);
        $this->manager->persist($entity);
        $this->addReference('network_' . $nid, $entity);
        return $entity;
    }
}
