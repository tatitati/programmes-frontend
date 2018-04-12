<?php
declare(strict_types=1);

namespace App\Builders;

use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use Faker\Factory;

class SegmentEventBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();

        $this->classTarget = SegmentEvent::class;
        // configure order of params to use Segment Builder constructor. You are free to choose the key names, but not the order.
        $this->blueprintConstructorTarget = [
            'pid' => new Pid($faker->regexify('[0-9b-df-hj-np-tv-z]{8,15}')),
            'version' => VersionBuilder::any()->build(),
            'segment' => MusicSegmentBuilder::any()->build(),
            'synopses' => new Synopses($faker->sentence(5), $faker->sentence(7), $faker->sentence(10)),
            'title' => null,
            'isChapter' => $faker->boolean(),
            'offset' => null,
            'position' => null,
        ];
    }
}
