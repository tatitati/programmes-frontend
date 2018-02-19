<?php
declare(strict_types=1);

namespace Tests\App\DataFixtures\ORM\ProgrammeEpisodes;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Episode;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\ProgrammeContainer;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ProgrammeItemFixture extends AbstractFixture implements DependentFixtureInterface
{
    /** @var ObjectManager $manager */
    private $manager;

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            ProgrammeContainerFixture::class,
        ];
    }

    /**
     * Create next tree:
     *      - Bra1
     *          - Ep1
     *          - Ser1
     *              - Ep1
     *              - Ep2
     *          - Ser2
     *              - Ser1 (nested series)
     *                  - Ep1
     *                  - Ep2
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        // series
        $series1 = $this->getReference('b0000sr1');
        $nestedSeries = $this->getReference('b000sr21');
        $brand = $this->getReference('b006q2x0');

        // standalone episodes (not nested in any series)
        $this->buildEpisode('p3000000', 'B1-E1', $brand);

        // Series with simple nested episodes
        $this->buildEpisode('p3000001', 'B1-S1-E1', $series1);
        $this->buildEpisode('p3000002', 'B1-S1-E2', $series1);

        // Series with more nested series
        $this->buildEpisode('p3000003', 'B1-S2-S1-E1', $nestedSeries);
        $this->buildEpisode('p3000004', 'B1-S2-S1-E2', $nestedSeries);


        $episode = new Episode('p01l1z04', 'The Day of the Doctor');
        $this->manager->persist($episode);

        $this->manager->flush();
    }

    private function buildEpisode(
        string $pid,
        string $title,
        ProgrammeContainer $series
    ): Episode {
        $episode = new Episode($pid, $title);
        $episode->setParent($series);
        $this->manager->persist($episode);
        $this->addReference($pid, $episode);
        return $episode;
    }
}
