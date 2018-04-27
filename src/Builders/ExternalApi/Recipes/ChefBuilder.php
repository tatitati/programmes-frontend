<?php

namespace App\Builders\ExternalApi\Recipes;

use App\Builders\AbstractBuilder;
use App\ExternalApi\Recipes\Domain\Chef;
use Faker\Factory;

class ChefBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();

        $this->classTarget = Chef::class;
        $this->blueprintConstructorTarget = [
            'id' => $faker->word,
            'name' => $faker->name,
            'image' => null,
        ];
    }
}
