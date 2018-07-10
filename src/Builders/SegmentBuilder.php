<?php
declare(strict_types = 1);

namespace App\Builders;

use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use Faker\Factory;

class SegmentBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();

        $this->classTarget = Segment::class;

        $this->blueprintConstructorTarget = [
            'dbId' => $faker->numberBetween(0, 10000),
            'pid' => new Pid($faker->regexify('[0-9b-df-hj-np-tv-z]{8,15}')),
            'type' => $faker->word,
            'synopses' => new Synopses($faker->sentence(5), $faker->sentence(15), $faker->sentence(30)),
            'contributionsCount' => $faker->numberBetween(0, 20),
            'title' => null,
            'duration' => null,
            'contributions' => null,
        ];
    }
}
