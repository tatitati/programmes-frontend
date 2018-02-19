<?php
declare(strict_types=1);

namespace Tests\App\DataFixtures\ORM\ProgrammeEpisodes;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Brand;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Series;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class ProgrammeContainerFixture extends AbstractFixture
{
    /** @var ObjectManager $manager */
    private $manager;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $brand1 = new Brand('b006q2x0', 'B1');
        $brand1->setAvailableEpisodesCount(2);
        $this->manager->persist($brand1);
        $this->addReference('b006q2x0', $brand1);

        $brand3 = new Brand('b006pnjk', 'B2');
        $this->manager->persist($brand3);

        $this->manager->flush();

        // a simple series
        $this->buildSeries('b0000sr1', 'B1-S1', $this->getReference('b006q2x0'));
        $this->buildSeries('b0000sr2', 'B1-S2', $this->getReference('b006q2x0'));

        $this->manager->flush();

        // a series with nested series
        $this->buildSeries('b000sr21', 'B1-S2-S1', $this->getReference('b0000sr2'));
        $this->buildSeries('b000sr22', 'B1-S2-S2', $this->getReference('b0000sr2'));

        $this->manager->flush();
    }

    private function buildSeries(
        string $pid,
        string $title,
        ProgrammeContainer $brand
    ) {
        $series = new Series($pid, $title);
        $series->setParent($brand);
        $this->manager->persist($series);
        $this->addReference($pid, $series);
        return $brand;
    }
}
