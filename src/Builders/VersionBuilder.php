<?php

namespace App\Builders;

use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Faker\Factory;

class VersionBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();

        $this->classTarget = Version::class;
        // configure order of params to use Version constructor. You are free to choose the key names, but not the order.
        $this->blueprintConstructorTarget = [
            'id' => $faker->numberBetween(1, 12345),
            'pid' => new Pid($faker->regexify('[0-9b-df-hj-np-tv-z]{8,15}')),
            'programmeItem' => $faker->boolean ? ClipBuilder::any()->build() : EpisodeBuilder::any()->build(),
            'isStreamable' => $faker->boolean,
            'isDownloadable' => $faker->boolean,
            'segmentEventCount' => $faker->numberBetween(1, 12345),
            'contributionsCount' => $faker->numberBetween(1, 12345),
            'duration' => null,
            'guidanceWarningCodes' => null,
            'hasCompetitionWarning' => $faker->boolean,
            'streamableFrom' => null,
            'streamableUntil' => null,
            'versionTypes' => null,
        ];
    }
}
