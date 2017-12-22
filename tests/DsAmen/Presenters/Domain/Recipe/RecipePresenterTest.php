<?php
declare(strict_types=1);

namespace Tests\App\DsAmen\Presenters\Domain\Recipe;

use App\DsAmen\Presenters\Domain\Recipe\RecipePresenter;
use App\ExternalApi\Recipes\Domain\Recipe;
use PHPUnit\Framework\TestCase;

class RecipePresenterTest extends TestCase
{
    public function testNoBranding(): void
    {
        $recipe = $this->createMock(Recipe::class);
        $presenter = new RecipePresenter($recipe, ['branding_name' => '']);
        $this->assertEquals('', $presenter->getBrandingBoxClass());
        $this->assertEquals('', $presenter->getTextBrandingClass());
    }
}
