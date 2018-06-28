<?php
declare(strict_types = 1);

namespace Tests\App\DataFixtures\PagesService;

use App\Builders\BrandBuilder;
use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;

class BrandsFixtures
{
    public static function eastEnders(): Brand
    {
        return new Brand(
            [1],
            new Pid('b006m86d'),
            'EastEnders',
            'EastEnders',
            new Synopses(
                'Welcome to Walford, E20.',
                'Welcome to Walford, E20.'
            ),
            ImagesFixtures::eastenders(),
            0, // promotions count
            0, //related links count
            false, //hasSupportingContent
            true, // isStreamable
            true, // isStreamableAlternate
            0, //ContributionsCount
            0, //agg broadcasts count
            5, // aggregated episodes count
            0, //available clips count
            1, //available episodes count
            0, //aggregated galleries count
            false,
            OptionsFixture::eastEnders(),
            null, //parent
            null, //position
            MasterBrandsFixtures::bbcOne(),
            [], //genres
            [], //formats
            null, //first broadcast date
            5 //expected child count
        );
    }

    public static function bookOfTheWeek()
    {
        return new Brand(
            [2],
            new Pid('b006qftk'),
            'Book of the Week',
            'Book of the Week',
            new Synopses(
                'Serialised book readings, featuring works from various genres',
                'Serialised book readings, featuring works of non-fiction, biography, autobiography, travel, diaries, essays, humour and history'
            ),
            ImagesFixtures::bookOfTheWeek(),
            0, // promotions count
            0, //related links count
            false, //hasSupportingContent
            true, // isStreamable
            false, // isStreamableAlternate
            0, //ContributionsCount
            0, //agg broadcasts count
            500, // aggregated episodes count
            0, //available clips count
            30, //available episodes count
            0, //aggregate galleries count
            false,
            OptionsFixture::empty(),
            null, //parent
            null, //position
            MasterBrandsFixtures::radioFour(),
            null, //genres
            null, //formats
            null, //first broadcast date
            null //expected child count
        );
    }

    public static function hardTalk(): Brand
    {
        return new Brand(
            [3],
            new Pid('p004t1s0'),
            'HARDtalk',
            'HARDtalk',
            new Synopses(
                'In-depth, hard-hitting interviews with newsworthy personalities.',
                'In-depth, hard-hitting interviews with newsworthy personalities.'
            ),
            ImagesFixtures::hardTalk(),
            0,
            6,
            false,
            true,
            false,
            0,
            0,
            1005,
            5,
            297,
            0,
            false,
            OptionsFixture::worldServiceRadio(),
            null,
            null,
            MasterBrandsFixtures::worldService(),
            []
        );
    }

    public static function wordsAndMusic(): Brand
    {
        return BrandBuilder::any()->with([
            'pid' => new Pid('b006x35f'),
            'title' => 'Words and Music',
            'searchTitle' => 'Words and Music',
            'synopses' => new Synopses(
                'A sequence of classical music mixed with well-loved and less familiar poems and prose',
                'A sequence of classical music interspersed with well-loved and less familiar poems and prose read by leading actors',
                ''
            ),
            'image' => ImagesFixtures::realityIsNotWhatItSeems(),
            'promotionsCount' => 0,
            'relatedLinksCount' => 0,
            'hasSupportingContent' => false,
            'isStreamable' => true,
            'isStreamableAlternate' => false,
            'contributionsCount' => 0,
            'aggregatedBroadcastsCount' => 100,
            'aggregatedEpisodesCount' => 1,
            'availableClipsCount' => 0,
            'availableEpisodesCount' => 1,
            'aggregatedGalleriesCount' => 0,
            'isPodcastable' => false,
            'options' => OptionsFixture::radioThree(),
            // optional
            'parent' => null,
            'position' => null,
            'masterBrand' => MasterBrandsFixtures::radioThree(),
            'genres' => null,
            'formats' => null,
            'firstBroadcastDate' => null,
            'expectedChildCount' => null,
        ])->build();
    }
}
