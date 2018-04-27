<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Presenters\Domain\Recipe;

use App\Builders\ExternalApi\Recipes\RecipeBuilder;
use App\Ds2013\Presenters\Domain\Recipe\RecipePresenter;
use App\ExternalApi\Recipes\Domain\RecipeImage;
use PHPUnit\Framework\TestCase;

/**
 * @group recipes
 */
class RecipePresenterTest extends TestCase
{
    /**
     * getRecipeUrl()
     */
    public function testPresenterProvideRecipeUrl()
    {
        $recipe = RecipeBuilder::any()->with(['id' => 423423])->build();

        $recipeUrl = (new RecipePresenter($recipe))->getRecipeUrl();

        $this->assertEquals('https://www.bbc.co.uk/food/recipes/423423', $recipeUrl);
    }

    /**
     * getChefImageUrl()
     * @dataProvider recipeWithOrNotChefImageProvider
     */
    public function testCanBuildUrlChefImage($givenRecipe, $expectedChefImageUrl)
    {
        $chefImageUrl = (new RecipePresenter($givenRecipe))->getChefImageUrl();

        $this->assertSame($expectedChefImageUrl, $chefImageUrl);
    }

    public function recipeWithOrNotChefImageProvider()
    {
        $givenRecipeWithNoChefImage = RecipeBuilder::anyWithChef()->build();
        $givenRecipeWithChefImage = RecipeBuilder::anyWithChefImage(['id' => '999999'])->build();

        return [
            'Recipe with chef-image has a image url' => [$givenRecipeWithChefImage, 'https://ichef.bbci.co.uk/food/ic/food_1x1_50/chefs/999999_1x1.jpg'],
            'Recipe without chef-image has no image url' => [$givenRecipeWithNoChefImage, null],
        ];
    }

    /**
     * getChefName()
     * @dataProvider recipeWithOrNotChefProvider
     */
    public function testProvideChefName($givenRecipe, $expectedChefName)
    {
        $chefName = (new RecipePresenter($givenRecipe))->getChefName();

        $this->assertSame($expectedChefName, $chefName);
    }

    public function recipeWithOrNotChefProvider()
    {
        $givenRecipeWithNoChef = RecipeBuilder::any()->build();
        $givenRecipeWithChef = RecipeBuilder::anyWithChef(['name' => 'Jamie Oliver'])->build();

        return [
            'Recipe with chef has a chef-name' => [$givenRecipeWithChef, 'Jamie Oliver'],
            'Recipe without chef has not chef-name' => [$givenRecipeWithNoChef, null],
        ];
    }

    /**
     * getImage()
     */
    public function testProvideNullWhenAskingForNonExistantImage()
    {
        $recipe = RecipeBuilder::any()->build();

        $recipeImage = (new RecipePresenter($recipe))->getImage();

        $this->assertNull($recipeImage);
    }

    public function testProvideImageWhenAskingForExistanImage()
    {
        $recipe = RecipeBuilder::anyWithImage()->build();

        $recipeImage = (new RecipePresenter($recipe))->getImage();

        $this->assertInstanceOf(RecipeImage::class, $recipeImage);
    }
}
