<?php

namespace Tests\App\Builders\ExternalApi\Recipes;

use App\Builders\ExternalApi\Recipes\RecipeBuilder;
use App\ExternalApi\Recipes\Domain\Recipe;
use PHPUnit\Framework\TestCase;

/**
 * @group recipes
 */
class RecipeBuilderTest extends TestCase
{
    public function testBasicCreation()
    {
        $recipe = RecipeBuilder::any()->build();

        $this->assertInstanceOf(Recipe::class, $recipe);
    }

    public function testIsPossibleToPassOptionsToNestedElements()
    {
        $recipe = RecipeBuilder::anyWithChef(['name' => 'Ratatouille'])->build();

        $this->assertInstanceOf(Recipe::class, $recipe);
        $this->assertSame('Ratatouille', $recipe->getChef()->getName());
    }

    public function testTraingOfWiths()
    {
        $recipe = RecipeBuilder::anyWithChef([
            'name' => 'Ratatouille',
            'id' => 'chef1212',
        ])->with(['title' => 'recipe title'])->build();

        $this->assertInstanceOf(Recipe::class, $recipe);

        $this->assertSame('chef1212', $recipe->getChef()->getId());
        $this->assertSame('Ratatouille', $recipe->getChef()->getName());
        $this->assertSame('recipe title', $recipe->getTitle());
    }
}
