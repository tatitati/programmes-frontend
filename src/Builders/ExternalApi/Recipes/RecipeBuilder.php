<?php

namespace App\Builders\ExternalApi\Recipes;

use App\Builders\AbstractBuilder;
use App\ExternalApi\Recipes\Domain\Recipe;
use Faker\Factory;

class RecipeBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();

        $this->classTarget = Recipe::class;
        $this->blueprintConstructorTarget = [
            'id' => $faker->numberBetween(100, 500),
            'title' => $faker->sentence(3),
            'description' => $faker->sentence(5),
            'image' => null,
            'chef' => null,
        ];
    }

    public static function anyWithChefImage(array $chefImageOptions = [])
    {
        return self::any()->with([
            'chef' => ChefBuilder::any()->with([
                'image' => ChefImageBuilder::any()->with($chefImageOptions)->build(),
            ])->build(),
        ]);
    }

    public static function anyWithChef(array $chefOptions = [])
    {
        return self::any()->with([
            'chef' => ChefBuilder::any()->with($chefOptions)->build(),
        ]);
    }

    public static function anyWithImage(array $imageOptions = [])
    {
        return self::any()->with([
            'image' => RecipeImageBuilder::any()->with($imageOptions)->build(),
        ]);
    }
}
