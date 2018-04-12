<?php
declare(strict_types=1);

namespace App\Builders;

use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Faker\Factory;

class ContributionBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();

        $this->classTarget = Contribution::class;
        // configure order of params to use Contribution constructor. You are free to choose the key names, but not the order.
        $this->blueprintConstructorTarget = [
            'pid' => new Pid($faker->regexify('[0-9b-df-hj-np-tv-z]{8,15}')),
            'contributor' => ContributorBuilder::any()->build(),
            'contributedTo' => MusicSegmentBuilder::any()->build(),
            'creditRole' => $faker->word,
        ];
    }
}
