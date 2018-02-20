<?php

namespace App\Builders;

use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Cake\Chronos\Chronos;
use Faker\Factory;

class BroadcastBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();

        $this->classTarget = Broadcast::class;
        // configure order of params to use Broadcast constructor. You are free to choose the key names, but not the order.
        $time = new Chronos();
        $this->blueprintConstructorTarget = [
            'pid' => new Pid($faker->regexify('[0-9b-df-hj-np-tv-z]{8,15}')),
            'version' => VersionBuilder::any()->build(),
            'programmeItem' => $faker->boolean ? ClipBuilder::any()->build() : EpisodeBuilder::any()->build(),
            'service' => ServiceBuilder::any()->build(),
            'startAt' => $time,
            'endAt' => $time,
            'duration' => $faker->numberBetween(1, 7200),
            'isBlanked' => $faker->boolean,
            'isRepeat' => $faker->boolean,
        ];
    }
}
