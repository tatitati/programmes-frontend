<?php
declare(strict_types = 1);

namespace Tests\App\DataFixtures\PagesService;

use App\Builders\ImageBuilder;
use BBC\ProgrammesPagesService\Domain\Entity\Image;

class ImagesFixtures
{
    public static function bbcOneLogo(): Image
    {
        return ImageBuilder::default()
           ->withPid('p02lrnkz')
           ->withTitle('BBC One Logo')
           ->withShortSynopses('BBC One Logo')
           ->withLongestSynopsis('BBC One Logo')
           ->build();
    }

    public static function radioFourLogo(): Image
    {
        return ImageBuilder::default()
           ->withPid('p04drxhs')
            ->withTitle('Radio Four Logo')
           ->withShortSynopses('Radio Four Logo')
           ->withLongestSynopsis('Radio Four Logo')
           ->build();
    }

    public static function worldServiceLogo(): Image
    {
        return ImageBuilder::default()
           ->withPid('p02wkrw1')
           ->withTitle('BBC World Service logo')
           ->withShortSynopses('BBC World Service logo')
           ->withLongestSynopsis('BBC World Service logo')
           ->build();
    }

    public static function bookOfTheWeek(): Image
    {
        return ImageBuilder::default()
           ->withPid('p04p12bc')
           ->withTitle('Book of the Week')
           ->withShortSynopses('Image for Book of the Week Brand')
           ->withLongestSynopsis('Image for Book of the Week Brand')
           ->build();
    }

    public static function realityIsNotWhatItSeems(): Image
    {
        return ImageBuilder::default()
           ->withPid('p04hc8d1')
           ->withTitle('Reality is not what it seems')
           ->withShortSynopses('Reality is not what it seems')
           ->withLongestSynopsis('Reality is not what it seems')
           ->build();
    }

    public static function eastenders(): Image
    {
        return ImageBuilder::default()
           ->withPid('p01vg679')
           ->withTitle('Eastenders')
           ->withShortSynopses('Image for Eastenders Brand')
           ->withLongestSynopsis('Image for Eastenders Brand')
           ->build();
    }

    public static function hardTalk(): Image
    {
        return ImageBuilder::default()
           ->withPid('p01tgdld')
           ->withTitle('Hardtalk')
           ->withShortSynopses('Hardtalk interviews newsmakers and personalities from across the globe.')
           ->withLongestSynopsis('Hardtalk interviews newsmakers and personalities from across the globe.')
           ->build();
    }
}
