<?php
declare(strict_types = 1);

namespace Tests\App\DataFixtures\PagesService;

use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;

class SeriesFixtures
{
    public static function bookOfTheWeekRealityIsNotWhatItSeems(): Series
    {
        return new Series(
            [2, 17],
            new Pid('b084ntjl'),
            'Reality Is Not What It Seems',
            'Reality Is Not What It Seems',
            new Synopses(
                'Carlo Rovelli\'s provocative account of how science affects our understanding of the world',
                'Mark Meadows reads scientist Carlo Rovelli\'s provocative exploration of how constantly developing scientific knowledge has changed our understanding of the world around us.'
            ),
            ImagesFixtures::realityIsNotWhatItSeems(),
            0,
            0,
            false,
            true,
            false,
            0,
            0,
            5,
            0,
            5,
            0,
            true, //ispodcastable, not used yet
            OptionsFixture::empty(),
            BrandsFixtures::bookOfTheWeek(),
            null,
            MasterBrandsFixtures::radioFour(),
            null, // genres
            null, //formats
            null,
            5
        );
    }
}
