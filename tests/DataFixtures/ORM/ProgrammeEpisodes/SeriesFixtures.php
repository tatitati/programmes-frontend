<?php

namespace Tests\App\DataFixtures\ORM\ProgrammeEpisodes;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Series;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class SeriesFixtures extends AbstractFixture implements DependentFixtureInterface
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
            BrandFixtures::class,
        ];
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        // a simple series
        $this->addReference(
            'b0000sr1',
            $this->buildSeries('b0000sr1', 'B1-S1', $this->getReference('b006q2x0'))
        );

        $this->addReference(
            'b0000sr2',
            $this->buildSeries('b0000sr2', 'B1-S2', $this->getReference('b006q2x0'))
        );

        $this->addReference(
            'b0000sr3',
            $this->buildSeries('b0000sr3', 'S3', null)
        );

        $this->manager->flush();

        // a series with nested series
        $this->addReference(
            'b000sr21',
            $this->buildSeries('b000sr21', 'B1-S2-S1', $this->getReference('b0000sr2'))
        );

        $this->addReference(
            'b000sr22',
            $this->buildSeries('b000sr22', 'B1-S2-S2', $this->getReference('b0000sr2'))
        );

        $this->manager->flush();
    }

    private function buildSeries(
        string $pid,
        string $title,
        ? ProgrammeContainer $brand
    ) {
        $series = new Series($pid, $title);
        $series->setParent($brand);

        $this->manager->persist($series);
        return $series;
    }
}
