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
        return MasterBrandBuilder::any()->with([
            'mid' => new Mid('bbc_one_london'),
            'name' => 'BBC One London',
            'image' => ImagesFixtures::bbcOneLogo(),
            'network' => ServicesAndNetworksFixtures::networkBbcOne(),
            'streamableInPlayspace' => true,
            'competitionWarning' => null,
        ])->build();
    }

    public static function radioThree(): MasterBrand
    {
        return MasterBrandBuilder::any()->with([
            'mid' => new Mid('bbc_radio_three'),
            'name' => 'BBC Radio 3',
            'image' => ImagesFixtures::radioThreeLogo(),
            'network' => ServicesAndNetworksFixtures::networkRadioThree(),
            'streamableInPlayspace' => true,
            'competitionWarning' => VersionBuilder::any()->with([
                'pid' => new Pid('p00px5zn'),
            ])->build(),
        ])->build();
    }

    public static function radioFour(): MasterBrand
    {
        return MasterBrandBuilder::any()->with([
            'mid' => new Mid('bbc_radio_four'),
            'name' => 'BBC Radio 4',
            'image' => ImagesFixtures::radioFourLogo(),
            'network' => ServicesAndNetworksFixtures::networkRadioFour(),
            'streamableInPlayspace' => true,
            'competitionWarning' => null,
        ])->build();
    }

    public static function worldService(): MasterBrand
    {
        return MasterBrandBuilder::any()->with([
            'mid' => new Mid('bbc_world_service'),
            'name' => 'BBC World Service',
            'image' => ImagesFixtures::worldServiceLogo(),
            'network' => ServicesAndNetworksFixtures::networkWorldService(),
            'streamableInPlayspace' => true,
            'competitionWarning' => null,
        ])->build();
    }
}
