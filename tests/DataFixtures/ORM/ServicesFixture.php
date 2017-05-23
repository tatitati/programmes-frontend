<?php
declare(strict_types = 1);

namespace Tests\App\DataFixtures\ORM;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Service;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

class ServicesFixture extends AbstractFixture
{
    /** @var ObjectManager $manager */
    private $manager;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        // TV
        $this->buildService('bbc_one_london', 'p00fzl6p', 'BBC One London', 'National TV', 'audio_video');

        // National Radio
        $this->buildService('bbc_radio_two', 'p00fzl8v', 'BBC Radio 2', 'National Radio', 'audio');

        // Regional Radio
        $this->buildService('bbc_radio_cymru', 'p00fzl7b', 'BBC Radio Cymru', 'Regional Radio', 'audio');

        // Local Radio
        $this->buildService('bbc_radio_berkshire', 'p00fzl74', 'BBC Radio Berkshire', 'Local Radio', 'audio');

        $this->manager->flush();
    }

    private function buildService(
        string $sid,
        string $pid,
        string $title,
        string $type,
        string $mediaType
    ): Service {
        $entity = new Service($sid, $pid, $title, $type, $mediaType);
        $this->manager->persist($entity);
        $this->addReference($pid, $entity);
        return $entity;
    }
}
