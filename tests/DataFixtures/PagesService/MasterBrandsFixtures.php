<?php
declare(strict_types = 1);

namespace Tests\App\DataFixtures\PagesService;

use BBC\ProgrammesPagesService\Domain\Entity\MasterBrand;
use BBC\ProgrammesPagesService\Domain\ValueObject\Mid;

class MasterBrandsFixtures
{
    public static function bbcOne(): MasterBrand
    {
        return new MasterBrand(
            new Mid('bbc_one_london'),
            'BBC One London',
            ImagesFixtures::bbcOneLogo(),
            ServicesAndNetworksFixtures::networkBbcOne(),
            null
        );
    }

    public static function radioFour(): MasterBrand
    {
        return new MasterBrand(
            new Mid('bbc_radio_four'),
            'BBC Radio 4',
            ImagesFixtures::radioFourLogo(),
            ServicesAndNetworksFixtures::networkRadioFour(),
            null
        );
    }
}
