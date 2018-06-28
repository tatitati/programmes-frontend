<?php
declare(strict_types=1);

namespace Tests\App\DataFixtures\PagesService;

use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum;
use BBC\ProgrammesPagesService\Domain\ValueObject\PartialDate;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use DateTimeImmutable;

class ClipsFixture
{
    public static function eastendersAvailable()
    {
        return new Clip(
            [1],
            new Pid('clp00001'),
            'Available Eastenders Clip',
            'Available Eastenders Clip',
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
            0, //aggregatedGalleriesCount
            true,
            OptionsFixture::eastEnders(),
            EpisodesFixtures::eastendersAvailable(),
            3, // position
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
}
