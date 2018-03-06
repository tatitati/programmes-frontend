<?php
declare(strict_types = 1);
namespace Tests\App\DataFixtures\ORM;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Clip;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Episode;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\MasterBrand;
use DateTime;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ProgrammeItemsFixture extends AbstractFixture implements DependentFixtureInterface
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

        $masterBrand = $this->getReference('masterbrand_p1000001'); //Radio Master Brand
        $this->buildEpisode('p3000001', 'Yesterday Afternoon Episode', $masterBrand, null);
        $this->buildEpisode('p3000002', 'Early Episode', $masterBrand, new DateTime());
        $this->buildEpisode('p3000003', 'Afternoon Episode', $masterBrand, null);
        $this->buildEpisode('p3000004', 'Tomorrow Early Episode', $masterBrand, null);

        $this->manager->flush();
    }

    private function buildClip(
        string $pid,
        string $title,
        MasterBrand $masterBrand
    ): Clip {
        $clip = new Clip($pid, $title);
        $clip->setMasterBrand($masterBrand);
        $this->manager->persist($clip);
        $this->addReference($pid, $clip);
        return $clip;
    }

    private function buildEpisode(
        string $pid,
        string $title,
        MasterBrand $masterBrand,
        ?DateTime $firstBroadcastDate
    ): Episode {
        $episode = new Episode($pid, $title);
        $episode->setMasterBrand($masterBrand);
        if (!is_null($firstBroadcastDate)) {
            $episode->setFirstBroadcastDate($firstBroadcastDate);
        }
        $this->manager->persist($episode);
        $this->addReference($pid, $episode);
        return $episode;
    }
}
