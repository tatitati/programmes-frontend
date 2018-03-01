<?php
declare(strict_types=1);

namespace Tests\App\DataFixtures\ORM\ProgrammeEpisodes;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Brand;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class BrandFixtures extends AbstractFixture
{
    /** @var ObjectManager $manager */
    private $manager;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $this->addReference(
            'b006q2x0',
            $this->buildBrand('b006q2x0', 'B1', 2)
        );

        $this->buildBrand('b006pnjk', 'B2');

        $this->manager->flush();
    }

    public function buildBrand($pid, $title, $countEpisodes = 0)
    {
        $brand = new Brand($pid, $title);
        $brand->setAvailableEpisodesCount($countEpisodes);

        $this->manager->persist($brand);

        return $brand;
    }
}
