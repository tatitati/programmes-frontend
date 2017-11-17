<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Recipes\Mapper;

use App\ExternalApi\Recipes\Domain\Chef;
use App\ExternalApi\Recipes\Domain\ChefImage;
use App\ExternalApi\Recipes\Domain\Recipe;
use App\ExternalApi\Recipes\Domain\RecipeImage;
use App\ExternalApi\Recipes\Mapper\RecipeMapper;
use PHPUnit\Framework\TestCase;
use stdClass;

class RecipeMapperTest extends TestCase
{
    /** @var stdClass */
    private $json;

    public function setUp()
    {
        $this->json = json_decode(file_get_contents(dirname(__DIR__) . '/JSON/bakeoff.json'));
    }

    public function testMapItem()
    {
        $pages = $this->json;
        $recipeMapper = new RecipeMapper();

        $items = [];
        foreach ($pages->byProgramme->b013pqnm->recipes as $recipe) {
            $items[] = $recipeMapper->mapItem($recipe);
        }

        $this->assertCount(4, $items);

        $this->assertInstanceOf(Recipe::class, $items[0]);
        $this->assertEquals('Stollen', $items[0]->getTitle());
        $this->assertInstanceOf(RecipeImage::class, $items[0]->getImage());
        $this->assertEquals(
            'https://ichef.bbci.co.uk/food/ic/food_16x9_200/recipes/stollen_27553_16x9.jpg',
            $items[0]->getImage()->getUrl('200')
        );

        $this->assertInstanceOf(Chef::class, $items[0]->getChef());
        $this->assertEquals('Paul Hollywood', $items[0]->getChef()->getName());
        $this->assertInstanceOf(ChefImage::class, $items[0]->getChef()->getImage());
        $this->assertEquals(
            'https://ichef.bbci.co.uk/food/ic/food_1x1_200/chefs/paul_hollywood_1x1.jpg',
            $items[0]->getChef()->getImage()->getUrl('200')
        );

        $this->assertEquals('Apple and cinnamon kugelhopf with honeyed apples', $items[3]->getTitle());
        $this->assertNull($items[3]->getImage());
    }
}
