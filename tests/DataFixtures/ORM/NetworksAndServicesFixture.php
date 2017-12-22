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
        // TV
        $n1 = $this->buildNetwork('bbc_one', 'BBC One', 'TV', NetworkMediumEnum::TV, null, null, 'bbcone');
        $s1 = $this->buildService('bbc_one_london', 'p00fzl6p', 'BBC One London', 'National TV', 'audio_video', $n1);
        $n1->setDefaultService($s1);
        $this->manager->persist($n1);

        // National Radio
        $n2 = $this->buildNetwork('bbc_radio_two', 'BBC Radio 2', 'National Radio', NetworkMediumEnum::RADIO, null, null, 'radio2');
        $s2 = $this->buildService('bbc_radio_two', 'p00fzl8v', 'BBC Radio 2', 'National Radio', 'audio', $n2, new DateTime('2000-04-08 02:03:04'), new DateTime('3000-04-08 02:03:05'));
        $n2->setDefaultService($s2);
        $this->manager->persist($n2);

        // Regional Radio
        $n3 = $this->buildNetwork('bbc_radio_cymru', 'BBC Radio Cymru', 'Regional Radio', NetworkMediumEnum::RADIO, null, null, 'radiocymru');
        $s3 = $this->buildService('bbc_radio_cymru', 'p00fzl7b', 'BBC Radio Cymru', 'Regional Radio', 'audio', $n3);
        $n3->setDefaultService($s3);
        $this->manager->persist($n3);

        // Local Radio
        $n4 = $this->buildNetwork('bbc_radio_berkshire', 'BBC Radio Berkshire', 'Local Radio', NetworkMediumEnum::RADIO, null, null, 'radioberkshire');
        $s4 = $this->buildService('bbc_radio_berkshire', 'p00fzl74', 'BBC Radio Berkshire', 'Local Radio', 'audio', $n4);
        $n4->setDefaultService($s4);
        $this->manager->persist($n4);

        // Network with start and end
        $n5 = $this->buildNetwork('bbc_radio_five_live_olympics_extra', '5 live Olympics Extra', 'National Radio', NetworkMediumEnum::RADIO, new DateTime('2012-07-25 00:00:00'), new DateTime('2012-08-13 22:59:59'), '5liveolympicsextra');
        $s5 = $this->buildService('bbc_radio_five_live_olympics_extra', 'p00rfdrb', '5 live Olympics Extra', 'National Radio', 'audio', $n5, new DateTime('2012-07-25 21:00:00'), new DateTime('2012-08-13 23:00:00'));
        $n5->setDefaultService($s5);
        $this->manager->persist($n5);

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
        DateTime $endDate = null,
        string $urlKey = null
    ): Network {
        $networkEntity = new Network($nid, $title);
        $networkEntity->setPosition(1);
        $networkEntity->setType($type);
        $networkEntity->setMedium($medium);
        $networkEntity->setStartDate($startDate);
        $networkEntity->setEndDate($endDate);
        $networkEntity->setUrlKey($urlKey);

        $this->manager->persist($networkEntity);
        $this->addReference('network_' . $nid, $networkEntity);

        return $networkEntity;
    }
}
