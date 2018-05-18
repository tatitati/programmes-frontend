<?php
declare(strict_types=1);

namespace Tests\App\DataFixtures\ORM\ProgrammeEpisodes;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Brand;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\App\DataFixtures\ORM\MasterBrandsFixture;

class BrandFixtures extends AbstractFixture implements DependentFixtureInterface
{
    /** @var ObjectManager $manager */
    private $manager;

    public function getDependencies()
    {
        return [
            MasterBrandsFixture::class,
        ];
    }

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

    public function buildBrand($pid, $title, $countEpisodes = 0, bool $isPodcastable = true, string $description = 'this is a short description')
    {
        $brand = new Brand($pid, $title);
        $brand->setAvailableEpisodesCount($countEpisodes);
        $brand->setIsPodcastable($isPodcastable);
        $brand->setShortSynopsis($description);
        $brand->setMasterBrand($this->getReference('masterbrand_p1000001'));

        $this->manager->persist($brand);

        return $brand;
    }
}
