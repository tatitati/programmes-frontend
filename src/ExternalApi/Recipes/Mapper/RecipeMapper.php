<?php
declare(strict_types=1);

namespace App\ExternalApi\Recipes\Mapper;

use App\ExternalApi\Recipes\Domain\Chef;
use App\ExternalApi\Recipes\Domain\ChefImage;
use App\ExternalApi\Recipes\Domain\Recipe;
use App\ExternalApi\Recipes\Domain\RecipeImage;
use stdClass;

class RecipeMapper
{
    public function mapItem(stdClass $recipe): Recipe
    {
        $chef = null;

        if ($recipe->chef) {
            $chefImage = null;

            if (($recipe->chef->hasImage ?? null) === 'true') {
                $chefImage = new ChefImage($recipe->chef->id);
            }

            $chef = new Chef($recipe->chef->id, $recipe->chef->displayName, $chefImage);
        }

        $recipeImage = null;

        if (($recipe->hasImage ?? null) === 'true') {
            $recipeImage = new RecipeImage($recipe->id);
        }

        return new Recipe(
            $recipe->id,
            $recipe->title ?? '',
            $recipe->description ?? '',
            $recipeImage,
            $chef
        );
    }
}
