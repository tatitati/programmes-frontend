<?php
declare(strict_types = 1);
namespace Tests\App\DataFixtures\ORM;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Broadcast;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Version;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Service;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

class BroadcastsFixture extends AbstractFixture implements DependentFixtureInterface
{
    /** @var ObjectManager $manager */
    private $manager;

    public function getDependencies()
    {
        return [
            ServicesFixture::class,
            VersionsFixture::class,
            ProgrammeItemsFixture::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $this->buildRadioSchedule();
        $this->buildTVSchedule();

        $this->manager->flush();
    }

    private function buildBroadcast(
        string $pid,
        Version $version,
        DateTime $startDateTime,
        DateTime $endDateTime,
        Service $service,
        ProgrammeItem $programmeItem
    ) {
        $entity = new Broadcast($pid, $version, $startDateTime, $endDateTime);
        $entity->setService($service);
        $entity->setProgrammeItem($programmeItem);
        $this->manager->persist($entity);
        $this->addReference($pid, $entity);
        return $entity;
    }

    private function buildRadioSchedule()
    {
        $service = $this->getReference('p00fzl8v'); //bbc_radio_two

        $version = $this->getReference('p4000001');
        $programmeItem = $this->getReference('p3000001'); //Yesterday Afternoon Episode
        $this->buildBroadcast('p5000001', $version, new DateTime('2017-05-21 14:00:00'), new DateTime('2017-05-21 14:45:00'), $service, $programmeItem);

        $version = $this->getReference('p4000002');
        $programmeItem = $this->getReference('p3000002'); //Early Episode
        $this->buildBroadcast('p5000002', $version, new DateTime('2017-05-22 02:00:00'), new DateTime('2017-05-22 02:45:00'), $service, $programmeItem);

        $version = $this->getReference('p4000003');
        $programmeItem = $this->getReference('p3000003'); //Afternoon Episode
        $this->buildBroadcast('p5000003', $version, new DateTime('2017-05-22 14:00:00'), new DateTime('2017-05-22 14:45:00'), $service, $programmeItem);

        $version = $this->getReference('p4000004');
        $programmeItem = $this->getReference('p3000004'); //Tomorrow Early Episode
        $this->buildBroadcast('p5000004', $version, new DateTime('2017-05-23 02:00:00'), new DateTime('2017-05-23 02:45:00'), $service, $programmeItem);
    }

    private function buildTvSchedule()
    {
        $service = $this->getReference('p00fzl6p'); //bbc_radio_two

        $version = $this->getReference('p4000001');
        $programmeItem = $this->getReference('p3000001'); //Yesterday Afternoon Episode
        $this->buildBroadcast('p5100001', $version, new DateTime('2017-05-21 14:00:00'), new DateTime('2017-05-21 14:45:00'), $service, $programmeItem);

        $version = $this->getReference('p4000002');
        $programmeItem = $this->getReference('p3000002'); //Early Episode
        $this->buildBroadcast('p5100002', $version, new DateTime('2017-05-22 02:00:00'), new DateTime('2017-05-22 02:45:00'), $service, $programmeItem);

        $version = $this->getReference('p4000003');
        $programmeItem = $this->getReference('p3000003'); //Afternoon Episode
        $this->buildBroadcast('p5100003', $version, new DateTime('2017-05-22 14:00:00'), new DateTime('2017-05-22 14:45:00'), $service, $programmeItem);

        $version = $this->getReference('p4000004');
        $programmeItem = $this->getReference('p3000004'); //Tomorrow Early Episode
        $this->buildBroadcast('p5100004', $version, new DateTime('2017-05-23 02:00:00'), new DateTime('2017-05-23 02:45:00'), $service, $programmeItem);
    }
}
