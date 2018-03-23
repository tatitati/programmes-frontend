<?php
declare(strict_types = 1);

namespace Tests\App\DataFixtures\PagesService;

use App\Builders\GalleryBuilder;
use BBC\ProgrammesPagesService\Domain\Entity\Gallery;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;

class GalleriesFixtures
{
    public static function eastenders(): Gallery
    {
        return GalleryBuilder::any()->with([
                'pid' => new Pid('p0000001'),
                'dbAncestryIds' => [1],
                'title' => 'A Gallery of Eastenders',
                'synopses' => new Synopses('Short Synopsis', 'Medium Synopsis', 'Long Synopsis'),
                'image' => ImagesFixtures::eastenders(),
                'options' => OptionsFixture::eastEnders(),
                'parent' => BrandsFixtures::eastEnders(),
                'masterBrand' => MasterBrandsFixtures::bbcOne(),
            ])->build();
    }
}
