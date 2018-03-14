<?php

namespace App\Builders;

use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use Cake\Chronos\Chronos;
use Faker\Factory;

class CollapsedBroadcastBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();

        $this->classTarget = CollapsedBroadcast::class;
        // configure order of params to use Broadcast constructor. You are free to choose the key names, but not the order.
        $time = new Chronos();
        $this->blueprintConstructorTarget = [
            'programmeItem' => $faker->boolean ? ClipBuilder::any()->build() : EpisodeBuilder::any()->build(),
            'services' => [ServiceBuilder::any()->build()],
            'startAt' => $time,
            'endAt' => $time,
            'isBlanked' => $faker->boolean,
            'isRepeat' => $faker->boolean,
        ];
    }

    public static function anyOfClip()
    {
        $self = new self();

        return $self->with([
            'programmeItem' => ClipBuilder::any()->build(),
        ]);
    }

    public static function anyOfEpisode()
    {
        $self = new self();

        return $self->with([
            'programmeItem' => EpisodeBuilder::any()->build(),
        ]);
    }
}
