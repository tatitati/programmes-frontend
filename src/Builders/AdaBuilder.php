<?php
namespace App\Builders;

use App\ExternalApi\Ada\Domain\AdaClass;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Faker\Factory;

class AdaBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();

        $this->classTarget = AdaClass::class;

        $this->blueprintConstructorTarget = [
            'id' => $faker->word,
            'title' => $faker->sentence(3),
            'programmeItemCount' => $faker->numberBetween(0, 100),
            'programmeItemCountContext' => $faker->randomElement([null, new Pid($faker->regexify('[0-9b-df-hj-np-tv-z]{8,15}'))]),
            'image' => ImageBuilder::any()->build(),
        ];
    }
}
