<?php
declare(strict_types = 1);

namespace Tests\App\DataFixtures\PagesService;

use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;

class ImagesFixtures
{
    public static function bbcOneLogo(): Image
    {
        return new Image(
            new Pid('p02lrnkz'),
            'BBC One Logo',
            'BBC One Logo',
            'BBC One Logo',
            'standard',
            'jpg'
        );
    }

    public static function radioFourLogo(): Image
    {
        return new Image(
            new Pid('p04drxhs'),
            'Radio Four Logo',
            'Radio Four Logo',
            'Radio Four Logo',
            'standard',
            'jpg'
        );
    }

    public static function bookOfTheWeek(): Image
    {
        return new Image(
            new Pid('p04p12bc'),
            'Book of the Week',
            'Image for Book of the Week Brand',
            'Image for Book of the Week Brand',
            'standard',
            'jpg'
        );
    }

    public static function realityIsNotWhatItSeems(): Image
    {
        return new Image(
            new Pid('p04hc8d1'),
            'Reality is not what it seems',
            'Reality is not what it seems',
            'Reality is not what it seems',
            'standard',
            'jpg'
        );
    }

    public static function eastenders(): Image
    {
        return new Image(
            new Pid('p01vg679'),
            'Eastenders',
            'Image for Eastenders Brand',
            'Image for Eastenders Brand',
            'standard',
            'jpg'
        );
    }
}
