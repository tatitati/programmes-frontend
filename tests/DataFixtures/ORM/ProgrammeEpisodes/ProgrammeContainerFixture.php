<?php
declare(strict_types=1);

namespace Tests\App\DataFixtures\ORM\ProgrammeEpisodes;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Brand;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class ProgrammeContainerFixture extends AbstractFixture
{
    /** @var ObjectManager $manager */
    private $manager;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $brand1 = new Brand('b006q2x0', 'Doctor Who');
        $brand1->setAvailableEpisodesCount(2);
        $this->manager->persist($brand1);

        $brand3 = new Brand('b006pnjk', 'DIY SOS');
        $this->manager->persist($brand3);

        $this->manager->flush();
    }
}
