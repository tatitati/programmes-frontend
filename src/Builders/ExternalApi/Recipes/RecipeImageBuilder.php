<?php

namespace App\Builders\ExternalApi\Recipes;

use App\Builders\AbstractBuilder;
use App\ExternalApi\Recipes\Domain\RecipeImage;
use Faker\Factory;

class RecipeImageBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();

        $this->classTarget = RecipeImage::class;
        $this->blueprintConstructorTarget = [
            'id' => $faker->word,
        ];
    }
}
