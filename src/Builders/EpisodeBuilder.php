<?php
declare(strict_types = 1);
namespace App\Builders;

use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Options;
use BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use Faker\Factory;

class EpisodeBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();

        $this->classTarget = Episode::class;
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
            'mediaType' => $faker->randomElement([MediaTypeEnum::VIDEO, MediaTypeEnum::AUDIO, MediaTypeEnum::UNKNOWN]),
            'segmentEventCount' => $faker->numberBetween(0, 8),
            'aggregatedBroadcastsCount' => $faker->numberBetween(1, 5),
            'availableClipsCount' => $faker->numberBetween(1, 5),
            'aggregatedGalleriesCount' => $faker->numberBetween(1, 5),
            'isExternallyEmbeddable' => $faker->boolean,
            'options' => new Options(),
            // optional
            'parent' => null,
            'position' => null,
            'masterBrand' => null,
            'genres' => null,
            'formats' => null,
            'firstBroadcastDate' => null,
            'releaseDate' => null,
            'duration' => $faker->numberBetween(500, 1000),
            'streamableFrom' => null,
            'streamableUntil' => null,
            'downloadableMediaSets' => [],
        ];
    }

    public static function anyRadioEpisode()
    {
        $self = new self();

        return $self->with([
            'masterBrand' => MasterBrandBuilder::anyRadioMasterBrand()->build(),
        ]);
    }

    public static function anyTVEpisode()
    {
        $self = new self();

        return $self->with([
            'masterBrand' => MasterBrandBuilder::anyTVMasterBrand()->build(),
        ]);
    }

    public static function anyWithPlayableDestination()
    {
        $faker = Factory::create();

        return self::any()->with([
            'isStreamable' => true,
            'mediaType' => $faker->randomElement([MediaTypeEnum::VIDEO, MediaTypeEnum::AUDIO]),
            'masterBrand' => MasterBrandBuilder::any()->with([
                'streamableInPlayspace' => true,
            ])->build(),
        ]);
    }
}
