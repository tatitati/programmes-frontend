<?php
declare(strict_types=1);

namespace Tests\App\DataFixtures\ORM\ProgrammeEpisodes;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Franchise;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class FranchiseFixture extends AbstractFixture
{
    /** @var ObjectManager $manager */
    private $manager;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $franchise = new Franchise('p00vyngt', 'BBC at the Edinburgh Festivals');
        $this->manager->persist($franchise);

        $this->manager->flush();
    }
}
