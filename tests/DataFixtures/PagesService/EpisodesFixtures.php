<?php
declare(strict_types = 1);

namespace Tests\App\DataFixtures\PagesService;

use App\Builders\EpisodeBuilder;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use DateTimeImmutable;

class EpisodesFixtures
{
    public static function eastendersAvailable(): Episode
    {
        return EpisodeBuilder::any()->with([
                'pid' => new Pid('p0000001'),
                'dbAncestryIds' => [1],
                'title' => 'An Episode of Eastenders',
                'synopses' => new Synopses('Short Synopsis', 'Medium Synopsis', 'Long Synopsis'),
                'image' => ImagesFixtures::eastenders(),
                'mediaType' => MediaTypeEnum::VIDEO,
                'options' => OptionsFixture::eastEnders(),
                'parent' => BrandsFixtures::eastEnders(),
                'position' => 2,
                'isStreamable' => true,
                'masterBrand' => MasterBrandsFixtures::bbcOne(),
                'streamableUntil' => new DateTimeImmutable('2017-07-01 20:00:00'),
            ])->build();
    }

    public static function beyondSpaceAndTimeAvailable()
    {
        return EpisodeBuilder::any()->with([
            'dbAncestryIds' => [2, 17, 222],
            'pid' => new Pid('b0849ccf'),
            'title' => 'Beyond Space and Time',
            'synopses' => new Synopses(
                'Carlo Rovelli\'s account of scientific discovery examines what happened before the Big Bang',
                'What happened before the Big Bang? Carlo Rovelli\'s account of scientific discovery takes us to the very frontiers of what we know about the creation of our universe.',
                "Do space and time truly exist? What is reality made of? Can we understand its deep texture? Scientist Carlo Rovelli has spent his whole life exploring these questions and pushing the boundaries of what we know.\n\nHe describes how our understanding of reality has changed throughout the centuries, from the philosophers and scientists of antiquity to contemporary researchers into quantum gravity. \n\nEpisode 5: Beyond Space and Time\nWhat happened before the Big Bang? Rovelli's account of scientific discovery takes us to the very frontiers of what we know about the creation of our universe.\n\nAuthor: Carlo Rovelli\nReader: Mark Meadows\nAbridger/Producer: Sara Davies\n\nA Pier production for BBC Radio 4."
            ),
            'image' => ImagesFixtures::realityIsNotWhatItSeems(),
            'mediaType' => MediaTypeEnum::AUDIO,
            'options' => OptionsFixture::empty(),
            'parent' => SeriesFixtures::bookOfTheWeekRealityIsNotWhatItSeems(),
            'position' => 5,
            'isStreamable' => true,
            'masterBrand' => MasterBrandsFixtures::radioFour(),
            'streamableFrom' => new DateTimeImmutable('2017-06-01 20:00:00'),
        ])->build();
    }
}
