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
            'duration' => $faker->numberBetween(100, 200),
            'isBlanked' => $faker->boolean,
            'isRepeat' => $faker->boolean,
        ];
    }

    public static function anyOfClip()
    {
        return self::any()->with([
            'programmeItem' => ClipBuilder::any()->build(),
        ]);
    }

    public static function anyOfEpisode()
    {
        return self::any()->with([
            'programmeItem' => EpisodeBuilder::any()->build(),
        ]);
    }

    public static function anyLive()
    {
        return self::any()->with([
            'startAt'  => Chronos::yesterday(),
            'endAt' => Chronos::tomorrow(),
        ]);
    }

    public static function anyOnPast()
    {
        $yesterday = Chronos::yesterday();

        return self::any()->with([
            'startAt'  => $yesterday->modify('-1 days'),
            'endAt' => $yesterday,
        ]);
    }

    public static function anyOnFuture()
    {
        $tomorrow = Chronos::tomorrow();

        return self::any()->with([
            'startAt' => $tomorrow,
            'endAt' => $tomorrow->modify('+1 days'),
        ]);
    }
}
