<?php
declare(strict_types = 1);
namespace Tests\App\DataFixtures\ORM;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Broadcast;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\CollapsedBroadcast;
use DateTime;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class CollapsedBroadcastsFixture extends AbstractFixture implements DependentFixtureInterface
{
    /** @var ObjectManager $manager */
    private $manager;

    public function getDependencies()
    {
        return [
            VersionsFixture::class,
            ProgrammeItemsFixture::class,
            BroadcastsFixture::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $this->buildCollapsedBroadcasts();

        $this->manager->flush();
    }

    private function buildCollapsedBroadcasts()
    {
        /** @var Broadcast $broadcast */
        $broadcast = $this->getReference('p5100002');

        $collapsedBroadcast = new CollapsedBroadcast(
            $broadcast->getProgrammeItem(),
            (string) $broadcast->getId(),
            (string) $broadcast->getService()->getId(),
            '0',
            new DateTime('2017-05-22 02:00:00'),
            new DateTime('2017-05-22 02:45:00')
            // NOTE: These are copy/pasted from BroadcastsFixture due to issues with UtcDateTimeType
        );
        $collapsedBroadcast->setTleo($broadcast->getProgrammeItem());
        $this->manager->persist($collapsedBroadcast);
    }
}
