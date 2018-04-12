<?php
declare(strict_types=1);

namespace App\Builders;

use BBC\ProgrammesPagesService\Domain\Entity\Contributor;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Faker\Factory;

class ContributorBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();

        $this->classTarget = Contributor::class;
        // configure order of params to use Contributor constructor. You are free to choose the key names, but not the order.
        $this->blueprintConstructorTarget = [
            'dbId' => $faker->numberBetween(1, 10000),
            'pid' => new Pid($faker->regexify('[0-9b-df-hj-np-tv-z]{8,15}')),
            'type' => $faker->word,
            'name' => $faker->words(2, true),
            'sortName' => null,
            'givenName' => null,
            'familyName' => null,
            'musicBrainzId' => null,
        ];
    }
}
