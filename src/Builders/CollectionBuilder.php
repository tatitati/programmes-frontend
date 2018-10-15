<?php

namespace App\Builders;

use BBC\ProgrammesPagesService\Domain\Entity\Collection;
use BBC\ProgrammesPagesService\Domain\Entity\Options;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use Faker\Factory;

class CollectionBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();

        $this->classTarget = Collection::class;
        // configure order of params to use Group constructor. You are free to choose the key names, but no the order.
        $this->blueprintConstructorTarget = [
            'dbAncestryIds' => [$faker->randomNumber()],
            'pid' => new Pid($faker->regexify('[0-9b-df-hj-np-tv-z]{8,15}')),
            'title' => $faker->sentence(3),
            'searchTitle' => $faker->sentence(4),
            'synopses' => new Synopses($faker->sentence(5), $faker->sentence(15), $faker->sentence(30)),
            'image' => ImageBuilder::any()->build(),
            'promotionsCount' => $faker->numberBetween(0, 5),
            'relatedLinksCount' => $faker->numberBetween(1, 5),
            'hasSupportingContent' => $faker->boolean,
            'options' => new Options(),
            'isPodcastable' => $faker->boolean,
            // optional
            'masterBrand' => null,
            'parent' => null,
        ];
    }
}
