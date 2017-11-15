<?php
declare(strict_types = 1);

namespace Tests\App\DataFixtures\PagesService;

use App\Builders\EpisodeBuilder;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use DateTimeImmutable;

class EpisodesFixtures
{
    public static function eastendersAvailable(): Episode
    {
        return EpisodeBuilder::default()
            ->withPid('p0000001')
            ->withDbAncestryIds([1])
            ->withTitle('An Episode of Eastenders')
            ->withSynopses(new Synopses('Short Synopsis', 'Medium Synopsis', 'Long Synopsis'))
            ->withImage(ImagesFixtures::eastenders())
            ->withMediaType(MediaTypeEnum::VIDEO)
            ->withOptions(OptionsFixture::eastEnders())
            ->withParent(BrandsFixtures::eastEnders())
            ->withPosition(2)
            ->withMasterbrand(MasterBrandsFixtures::bbcOne())
            ->withStremableUntil(new DateTimeImmutable('2017-07-01 20:00:00'))
            ->build();
    }

    public static function beyondSpaceAndTimeAvailable()
    {
        return EpisodeBuilder::default()
            ->withDbAncestryIds([2, 17, 222])
            ->withPid('b0849ccf')
            ->withTitle('Beyond Space and Time')
            ->withSynopses(
                new Synopses(
                    'Carlo Rovelli\'s account of scientific discovery examines what happened before the Big Bang',
                    'What happened before the Big Bang? Carlo Rovelli\'s account of scientific discovery takes us to the very frontiers of what we know about the creation of our universe.',
                    "Do space and time truly exist? What is reality made of? Can we understand its deep texture? Scientist Carlo Rovelli has spent his whole life exploring these questions and pushing the boundaries of what we know.\n\nHe describes how our understanding of reality has changed throughout the centuries, from the philosophers and scientists of antiquity to contemporary researchers into quantum gravity. \n\nEpisode 5: Beyond Space and Time\nWhat happened before the Big Bang? Rovelli's account of scientific discovery takes us to the very frontiers of what we know about the creation of our universe.\n\nAuthor: Carlo Rovelli\nReader: Mark Meadows\nAbridger/Producer: Sara Davies\n\nA Pier production for BBC Radio 4."
                )
            )
            ->withImage(ImagesFixtures::realityIsNotWhatItSeems())
            ->withMediaType(MediaTypeEnum::AUDIO)
            ->withOptions(OptionsFixture::empty())
            ->withParent(SeriesFixtures::bookOfTheWeekRealityIsNotWhatItSeems())
            ->withPosition(5)
            ->withMasterbrand(MasterBrandsFixtures::radioFour())
            ->withStreamableFrom(new DateTimeImmutable('2017-06-01 20:00:00'))
            ->build();
    }
}
