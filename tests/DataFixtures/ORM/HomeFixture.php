<?php
declare(strict_types = 1);
namespace Tests\App\DataFixtures\ORM;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Network;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Service;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Episode;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

class HomeFixture extends AbstractFixture
{
    private $manager;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        // TV
        $tvService = $this->buildService('bbc_one_london', 'p00fzl6p', 'BBC One London', 'National TV', 'audio_video');
        $this->buildNetwork('bbc_one', 'BBC One', $tvService, 'TV');

        // National Radio
        $nationalService = $this->buildService('bbc_radio_two', 'p00fzl8v', 'BBC Radio 2', 'National Radio', 'audio');
        $this->buildNetwork('bbc_radio_two', 'BBC Radio 2', $nationalService, 'National Radio');

        // Regional Radio
        $regionalService = $this->buildService('bbc_radio_cymru', 'p00fzl7b', 'BBC Radio Cymru', 'Regional Radio', 'audio');
        $this->buildNetwork('bbc_radio_cymru', 'BBC Radio Cymru', $regionalService, 'Regional Radio');

        // Local Radio
        $localService = $this->buildService('bbc_radio_berkshire', 'p00fzl74', 'BBC Radio Berkshire', 'Local Radio', 'audio');
        $this->buildNetwork('bbc_radio_four', 'BBC Radio Berkshire', $localService, 'Local Radio');

        // Programmes
        $manager->persist(new Episode('b00pk64v', 'The Incredibles'));
        $manager->persist(new Episode('b016kd9s', '101 Dalmations'));

        $this->manager->flush();
    }

    private function buildService(
        $sid,
        $pid,
        $title,
        $type,
        $mediaType
    ) {
        $entity = new Service($sid, $pid, $title, $type, $mediaType);
        $this->manager->persist($entity);
        $this->addReference($pid, $entity);
        return $entity;
    }

    private function buildNetwork(
        $nid,
        $title,
        $defaultService,
        $type
    ) {
        $entity = new Network($nid, $title, $title);
        $entity->setDefaultService($defaultService);
        $entity->setPosition(1);
        $entity->setType($type);
        $this->manager->persist($entity);
        $this->addReference('network_' . $nid, $entity);
        return $entity;
    }
}
