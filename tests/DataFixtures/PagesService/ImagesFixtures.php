<?php
declare(strict_types = 1);

namespace Tests\App\DataFixtures\PagesService;

use App\Builders\ImageBuilder;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;

class ImagesFixtures
{
    public static function bbcOneLogo(): Image
    {
        return ImageBuilder::any()->with([
                'pid' => new Pid('p02lrnkz'),
                'title' => 'BBC One Logo',
                'shortSynopsis' => 'BBC One Logo',
                'longestSynopsis' => 'BBC One Logo',
        ])->build();
    }

    public static function radioThreeLogo(): Image
    {
        return ImageBuilder::any()->with([
            'pid' => new Pid('p04drxhn'),
            'title' => 'Radio Three Logo',
            'shortSynopsis' => 'Radio Three Logo',
            'longestSynopsis' => 'Radio Three Logo',
        ])->build();
    }

    public static function radioFourLogo(): Image
    {
        return ImageBuilder::any()->with([
             'pid' => new Pid('p04drxhs'),
             'title' => 'Radio Four Logo',
             'shortSynopsis' => 'Radio Four Logo',
             'longestSynopsis' => 'Radio Four Logo',
         ])->build();
    }

    public static function worldServiceLogo(): Image
    {
        return ImageBuilder::any()->with([
             'pid' => new Pid('p02wkrw1'),
             'title' => 'BBC World Service logo',
             'shortSynopsis' => 'BBC World Service logo',
             'longestSynopsis' => 'BBC World Service logo',
         ])->build();
    }

    public static function bookOfTheWeek(): Image
    {
        return ImageBuilder::any()->with([
             'pid' => new Pid('p04p12bc'),
             'title' => 'Book of the Week',
             'shortSynopsis' => 'Image for Book of the Week Brand',
             'longestSynopsis' => 'Image for Book of the Week Brand',
         ])->build();
    }

    public static function realityIsNotWhatItSeems(): Image
    {
        return ImageBuilder::any()->with([
             'pid' => new Pid('p04hc8d1'),
             'title' => 'Reality is not what it seems',
             'shortSynopsis' => 'Reality is not what it seems',
             'longestSynopsis' => 'Reality is not what it seems',
         ])->build();
    }

    public static function eastenders(): Image
    {
        return ImageBuilder::any()->with([
                'pid' => new Pid('p01vg679'),
                'title' => 'Eastenders',
                'shortSynopsis' => 'Image for Eastenders Brand',
                'longestSynopsis' => 'Image for Eastenders Brand',
        ])->build();
    }

    public static function hardTalk(): Image
    {
        return ImageBuilder::any()->with([
             'pid' => new Pid('p01tgdld'),
             'title' => 'Hardtalk',
             'shortSynopsis' => 'Hardtalk interviews newsmakers and personalities from across the globe.',
             'longestSynopsis' => 'Hardtalk interviews newsmakers and personalities from across the globe.',
         ])->build();
    }

    public static function theChessboard(): Image
    {
        return ImageBuilder::any()->with([
            'pid' => new Pid('p069d242'),
            'title' => 'The Chessboard',
            'shortSynopsis' => 'Readings from Adjoa Andoh and Henry Goodman. Music from Shostakovich to The Rolling Stones',
        ])->build();
    }
}
