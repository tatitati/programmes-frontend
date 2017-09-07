<?php
declare(strict_types=1);

namespace Tests\App\DataFixtures\ORM\FindByPid\Tlec;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Brand;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Clip;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\App\DataFixtures\ORM\BaseORMFixture;
use Tests\App\DataFixtures\ORM\EntityCreationHelper\BrandCreationHelper;
use Tests\App\DataFixtures\ORM\EntityCreationHelper\ClipCreationHelper;

class TlecClipsFixture extends BaseORMFixture
{
    public function load(ObjectManager $manager)
    {
        $brands = (new BrandCreationHelper())->create(4);

        // First brand has no clips
        $brand1 = array_shift($brands);
        $manager->persist($brand1);

        // Second brand has less than 4 clips
        /** @var Brand $brand2 */
        $brand2 = array_shift($brands);
        $brand2->setAvailableClipsCount(2);
        $manager->persist($brand2);

        $clipCreator = new ClipCreationHelper();

        $brand2clips = $clipCreator->create(2);

        /** @var Clip $clip */
        foreach ($brand2clips as $clip) {
            $clip->setParent($brand2);
            $clip->setStreamable(true);
            $clip->setDuration(10);
        }
        $this->persistEntities($manager, $brand2clips);

        // Third brand has 4 clips
        /** @var Brand $brand3 */
        $brand3 = array_shift($brands);
        $brand3->setAvailableClipsCount(4);
        $manager->persist($brand3);

        $brand3clips = $clipCreator->create(4);

        foreach ($brand3clips as $clip) {
            $clip->setParent($brand3);
            $clip->setStreamable(true);
            $clip->setDuration(10);
        }
        $this->persistEntities($manager, $brand3clips);

        // Fourth has more than 4 clips
        /** @var Brand $brand4 */
        $brand4 = array_shift($brands);
        $brand4->setAvailableClipsCount(6);
        $manager->persist($brand4);

        $brand4clips = $clipCreator->create(6);

        foreach ($brand4clips as $clip) {
            $clip->setParent($brand4);
            $clip->setStreamable(true);
            $clip->setDuration(10);
        }
        $this->persistEntities($manager, $brand4clips);

        $manager->flush();
    }
}
