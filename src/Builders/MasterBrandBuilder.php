<?php

namespace App\Builders;

use BBC\ProgrammesPagesService\Domain\Entity\MasterBrand;
use BBC\ProgrammesPagesService\Domain\Enumeration\NetworkMediumEnum;
use BBC\ProgrammesPagesService\Domain\ValueObject\Mid;
use Faker\Factory;

class MasterBrandBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();

        $this->classTarget = MasterBrand::class;
        $this->blueprintConstructorTarget = [
            'mid' => new Mid($faker->regexify('[0-9a-z_]+')),
            'name' => $faker->sentence(1),
            'image' => ImageBuilder::any()->build(),
            'network' => NetworkBuilder::any()->build(),
            'competitionWarning' => VersionBuilder::any()->build(),
        ];
    }

    public static function anyRadioMasterBrand()
    {
        $self = new self();

        return $self->with([
            'network' => NetworkBuilder::any()->with(['medium' => NetworkMediumEnum::RADIO])->build(),
        ]);
    }

    public static function anyTVMasterBrand()
    {
        $self = new self();

        return $self->with([
            'network' => NetworkBuilder::any()->with(['medium' => NetworkMediumEnum::TV])->build(),
        ]);
    }
}
