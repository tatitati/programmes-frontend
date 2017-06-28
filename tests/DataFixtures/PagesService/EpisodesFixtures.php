<?php
declare(strict_types = 1);

namespace Tests\App\DataFixtures\PagesService;

use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum;
use BBC\ProgrammesPagesService\Domain\ValueObject\PartialDate;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use DateTimeImmutable;

class EpisodesFixtures
{
    public static function eastendersAvailable(): Episode
    {
        return new Episode(
            [1],
            new Pid('p0000001'),
            'An Episode of Eastenders',
            'An Episode of Eastenders',
            new Synopses(
                'Short Synopsis',
                'Medium Synopsis',
                'Long Synopsis'
            ),
            ImagesFixtures::eastenders(),
            0,
            0,
            false,
            true, //isStreamable
            true, //isStremableAlternate
            0,
            MediaTypeEnum::VIDEO,
            0,
            0,
            0,
            0,
            BrandsFixtures::eastEnders(),
            2, // position
            MasterBrandsFixtures::bbcOne(),
            null, //genres
            null, //formats
            null, //First Broadcast date
            new PartialDate(2017, 6, 1),
            1800, //duration
            new DateTimeImmutable('2017-06-01 20:00:00'), //Streamable from
            new DateTimeImmutable('2017-07-01 20:00:00') //Streamable until
        );
    }

    public static function beyondSpaceAndTimeAvailable()
    {
        return new Episode(
            [2, 17, 222],
            new Pid('b0849ccf'),
            'Beyond Space and Time',
            'Beyond Space and Time',
            new Synopses(
                'Carlo Rovelli\'s account of scientific discovery examines what happened before the Big Bang',
                'What happened before the Big Bang? Carlo Rovelli\'s account of scientific discovery takes us to the very frontiers of what we know about the creation of our universe.',
                "Do space and time truly exist? What is reality made of? Can we understand its deep texture? Scientist Carlo Rovelli has spent his whole life exploring these questions and pushing the boundaries of what we know.\n\nHe describes how our understanding of reality has changed throughout the centuries, from the philosophers and scientists of antiquity to contemporary researchers into quantum gravity. \n\nEpisode 5: Beyond Space and Time\nWhat happened before the Big Bang? Rovelli's account of scientific discovery takes us to the very frontiers of what we know about the creation of our universe.\n\nAuthor: Carlo Rovelli\nReader: Mark Meadows\nAbridger/Producer: Sara Davies\n\nA Pier production for BBC Radio 4."
            ),
            ImagesFixtures::realityIsNotWhatItSeems(),
            0,
            0,
            false,
            true, //isStreamable
            false, //isStremableAlternate
            0,
            MediaTypeEnum::AUDIO,
            0,
            0,
            0,
            0,
            SeriesFixtures::bookOfTheWeekRealityIsNotWhatItSeems(),
            5, // position
            MasterBrandsFixtures::radioFour(),
            null, //genres
            null, //formats
            null, //First Broadcast date
            new PartialDate(2015, 6, 1),
            900, //duration
            new DateTimeImmutable('2017-06-01 20:00:00'), //Streamable from
            null //Streamable until
        );
    }
}
