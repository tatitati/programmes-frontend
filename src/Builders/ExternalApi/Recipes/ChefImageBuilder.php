<?php

namespace App\Builders\ExternalApi\Recipes;

use App\Builders\AbstractBuilder;
use App\ExternalApi\Recipes\Domain\ChefImage;
use Faker\Factory;

class ChefImageBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();

        $this->classTarget = ChefImage::class;
        $this->blueprintConstructorTarget = [
            'id' => $faker->word,
        ];
    }
}
