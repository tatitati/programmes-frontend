<?php
declare(strict_types = 1);
namespace Tests\App\DataFixtures\ORM;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Network;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Service;
use BBC\ProgrammesPagesService\Domain\Enumeration\NetworkMediumEnum;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

class NetworksAndServicesFixture extends AbstractFixture
{
    /** @var ObjectManager $manager */
    private $manager;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->createNetworks();
        $this->createServices();
        $this->setNetworkDefaultServices();
    }

    public function createNetworks()
    {
        // TV
        $this->buildNetwork('bbc_one', 'BBC One', 'TV', NetworkMediumEnum::TV);

        // National Radio
        $this->buildNetwork('bbc_radio_two', 'BBC Radio 2', 'National Radio', NetworkMediumEnum::RADIO);

        // Regional Radio
        $this->buildNetwork('bbc_radio_cymru', 'BBC Radio Cymru', 'Regional Radio', NetworkMediumEnum::RADIO);

        // Local Radio
        $this->buildNetwork('bbc_radio_berkshire', 'BBC Radio Berkshire', 'Local Radio', NetworkMediumEnum::RADIO);

        $this->manager->flush();
    }

    public function setNetworkDefaultServices()
    {
        $bbcOneService = $this->getReference('p00fzl6p');
        $bbcOne = $this->getReference('network_bbc_one');
        $bbcOne->setDefaultService($bbcOneService);
        $this->manager->persist($bbcOne);

        $radioTwoService = $this->getReference('p00fzl8v');
        $radioTwo = $this->getReference('network_bbc_radio_two');
        $radioTwo->setDefaultService($radioTwoService);
        $this->manager->persist($radioTwo);

        $cymruService = $this->getReference('p00fzl7b');
        $cymru = $this->getReference('network_bbc_radio_cymru');
        $cymru->setDefaultService($cymruService);
        $this->manager->persist($cymru);

        $localService = $this->getReference('p00fzl74');
        $localNetwork = $this->getReference('network_bbc_radio_berkshire');
        $localNetwork->setDefaultService($localService);
        $this->manager->persist($localNetwork);

        $this->manager->flush();
    }

    public function createServices()
    {
        // TV
        $this->buildService('bbc_one_london', 'p00fzl6p', 'BBC One London', 'National TV', 'audio_video', $this->getReference('network_bbc_one'));

        // National Radio
        $this->buildService('bbc_radio_two', 'p00fzl8v', 'BBC Radio 2', 'National Radio', 'audio', $this->getReference('network_bbc_radio_two'));

        // Regional Radio
        $this->buildService('bbc_radio_cymru', 'p00fzl7b', 'BBC Radio Cymru', 'Regional Radio', 'audio', $this->getReference('network_bbc_radio_cymru'));

        // Local Radio
        $this->buildService('bbc_radio_berkshire', 'p00fzl74', 'BBC Radio Berkshire', 'Local Radio', 'audio', $this->getReference('network_bbc_radio_berkshire'));

        $this->manager->flush();
    }

    private function buildService(
        string $sid,
        string $pid,
        string $title,
        string $type,
        string $mediaType,
        Network $network
    ): Service {
        $entity = new Service($sid, $pid, $title, $type, $mediaType);
        $entity->setNetwork($network);
        $this->manager->persist($entity);
        $this->addReference($pid, $entity);
        return $entity;
    }

    private function buildNetwork(
        string $nid,
        string $title,
        string $type,
        string $medium
    ): Network {
        $entity = new Network($nid, $title, $title);
        $entity->setPosition(1);
        $entity->setType($type);
        $entity->setMedium($medium);
        $this->manager->persist($entity);
        $this->addReference('network_' . $nid, $entity);
        return $entity;
    }
}
