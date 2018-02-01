<?php
declare(strict_types=1);

namespace Tests\App\DataFixtures\ORM\ProgrammeEpisodes;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Episode;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class ProgrammeItemFixture extends AbstractFixture
{
    /** @var ObjectManager $manager */
    private $manager;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $episode = new Episode('p01l1z04', 'The Day of the Doctor');
        $this->manager->persist($episode);

        $this->manager->flush();
    }
}
