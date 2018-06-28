<?php
declare(strict_types = 1);

namespace Tests\App\DataFixtures\PagesService;

use App\Builders\VersionBuilder;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\Entity\VersionType;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;

class VersionsFixture
{
    public static function eastendersEpisodeUnavailable(): Version
    {
        return VersionBuilder::any()
            ->with([
                'programmeItem' => EpisodesFixtures::eastendersUnavailable(),
                'isStreamable' => false,
                'isDownloadable' => false,
                'segmentEventCount' => 0,
            ])->build();
    }

    public static function eastendersEpisodeAvailable(): Version
    {
        $versionType = new VersionType('original', 'Original');
        return VersionBuilder::any()
            ->with([
                'programmeItem' => EpisodesFixtures::eastendersAvailable(),
                'pid' => new Pid('v0020001'),
                'isStreamable' => true,
                'isDownloadable' => false,
                'segmentEventCount' => 0,
                'hasCompetitionWarning' => false,
                'duration' => 1800,
                'versionTypes' => [$versionType],
            ])->build();
    }

    public static function eastendersClipAvailable(): Version
    {
        $versionType = new VersionType('original', 'Original');
        return VersionBuilder::any()
            ->with([
                'programmeItem' => ClipsFixture::eastendersAvailable(),
                'pid' => new Pid('v0010001'),
                'isStreamable' => true,
                'isDownloadable' => false,
                'segmentEventCount' => 0,
                'hasCompetitionWarning' => true,
                'duration' => 3600,
                'versionTypes' => [$versionType],
            ])->build();
    }

    public static function wordsAndMusicAvailable(): Version
    {
        $versionType = new VersionType('original', 'Original');
        return VersionBuilder::any()
            ->with([
                'programmeItem' => EpisodesFixtures::wordsAndMusicAvailable(),
                'pid' => new Pid('v0010002'),
                'isStreamable' => true,
                'isDownloadable' => false,
                'segmentEventCount' => 2,
                'hasCompetitionWarning' => false,
                'duration' => 4500,
                'versionTypes' => [$versionType],
            ])->build();
    }
}
