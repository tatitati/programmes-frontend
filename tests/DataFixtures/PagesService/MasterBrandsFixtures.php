<?php
declare(strict_types = 1);

namespace Tests\App\DataFixtures\PagesService;

use App\Builders\MasterBrandBuilder;
use App\Builders\VersionBuilder;
use BBC\ProgrammesPagesService\Domain\Entity\MasterBrand;
use BBC\ProgrammesPagesService\Domain\ValueObject\Mid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;

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

    public static function radioThree(): MasterBrand
    {
        return MasterBrandBuilder::any()->with([
            'mid' => new Mid('bbc_radio_three'),
            'name' => 'BBC Radio 3',
            'image' => ImagesFixtures::radioThreeLogo(),
            'network' => ServicesAndNetworksFixtures::networkRadioThree(),
            'competitionWarning' => VersionBuilder::any()->with([
                'pid' => new Pid('p00px5zn'),
            ])->build(),
        ])->build();
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

    public static function worldService(): MasterBrand
    {
        return new MasterBrand(
            new Mid('bbc_world_service'),
            'BBC World Service',
            ImagesFixtures::worldServiceLogo(),
            ServicesAndNetworksFixtures::networkWorldService(),
            null
        );
    }
}
