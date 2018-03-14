<?php

namespace App\Builders;

use BBC\ProgrammesPagesService\Domain\Entity\Options;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use Faker\Factory;

class SeriesBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();

        $this->classTarget = Series::class;
        // configure order of params to use Episode constructor. You are free to choose the key names, but no the order.
        $this->blueprintConstructorTarget = [
            'dbAncestryIds' => [$faker->randomNumber()],
            'pid' => new Pid($faker->regexify('[0-9b-df-hj-np-tv-z]{8,15}')),
            'title' => $faker->sentence(3),
            'searchTitle' => $faker->sentence(4),
            'synopses' => new Synopses($faker->sentence(5), $faker->sentence(15), $faker->sentence(30)),
            'image' => ImageBuilder::any()->build(),
            'promotionsCount' => $faker->numberBetween(0, 5),
            'relatedLinksCount' => $faker->numberBetween(1, 5),
            'hasSupportingContent' => $faker->boolean,
            'isStreamable' => $faker->boolean,
            'isStreamableAlternate' => $faker->boolean,
            'contributionsCount' => $faker->numberBetween(0, 5),
            'aggregatedBroadcastsCount' => $faker->numberBetween(0, 5),
            'aggregatedEpisodesCount' => $faker->numberBetween(0, 5),
            'availableClipsCount' => $faker->numberBetween(0, 5),
            'availableEpisodesCount' => $faker->numberBetween(0, 5),
            'aggregatedGalleriesCount' => $faker->numberBetween(0, 5),
            'isPodcastable' => $faker->boolean,
            'options' => new Options(),
            // optional
            'parent' => null,
            'position' => null,
            'masterBrand' => null,
            'genres' => null,
            'formats' => null,
            'firstBroadcastDate' => null,
            'expectedChildCount' => null,
        ];
    }

    public static function anyRadioSeries()
    {
        $self = new self();

        return $self->with([
            'masterBrand' => MasterBrandBuilder::anyRadioMasterBrand()->build(),
        ]);
    }

    public static function anyTVSeries()
    {
        $self = new self();

        return $self->with([
            'masterBrand' => MasterBrandBuilder::anyTVMasterBrand()->build(),
        ]);
    }
}
