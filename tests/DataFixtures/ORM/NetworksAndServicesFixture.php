<?php
declare(strict_types = 1);
namespace Tests\App\DataFixtures\ORM;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Network;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Service;
use BBC\ProgrammesPagesService\Domain\Enumeration\NetworkMediumEnum;
use DateTime;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

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

        // Network with start and end
        $this->buildNetwork('bbc_radio_five_live_olympics_extra', '5 live Olympics Extra', 'National Radio', NetworkMediumEnum::RADIO, new DateTime('2012-07-25 00:00:00'), new DateTime('2012-08-13 22:59:59'));

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

        $olympicService = $this->getReference('p00rfdrb');
        $olympicNetwork = $this->getReference('network_bbc_radio_five_live_olympics_extra');
        $olympicNetwork->setDefaultService($olympicService);
        $this->manager->persist($olympicNetwork);

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

        // Network with start and end
        $this->buildService('bbc_radio_five_live_olympics_extra', 'p00rfdrb', '5 live Olympics Extra', 'National Radio', 'audio', $this->getReference('network_bbc_radio_five_live_olympics_extra'), new DateTime('2012-07-25 21:00:00'), new DateTime('2012-08-13 23:00:00'));

        $this->manager->flush();
    }

    private function buildService(
        string $sid,
        string $pid,
        string $title,
        string $type,
        string $mediaType,
        Network $network,
        DateTime $startDate = null,
        DateTime $endDate = null
    ): Service {
        $entity = new Service($sid, $pid, $title, $type, $mediaType);
        $entity->setNetwork($network);
        $entity->setStartDate($startDate);
        $entity->setEndDate($endDate);
        $this->manager->persist($entity);
        $this->addReference($pid, $entity);
        return $entity;
    }

    private function buildNetwork(
        string $nid,
        string $title,
        string $type,
        string $medium,
        DateTime $startDate = null,
        DateTime $endDate = null
    ): Network {
        $entity = new Network($nid, $title);
        $entity->setPosition(1);
        $entity->setType($type);
        $entity->setMedium($medium);
        $entity->setStartDate($startDate);
        $entity->setEndDate($endDate);
        $this->manager->persist($entity);
        $this->addReference('network_' . $nid, $entity);
        return $entity;
    }
}
