<?php
declare(strict_types=1);

namespace App\Ds2013\Presenters\Domain\Recipe;

use App\Ds2013\Presenter;
use App\ExternalApi\Recipes\Domain\Recipe;
use App\ExternalApi\Recipes\Domain\RecipeImage;

class RecipePresenter extends Presenter
{
    /** @var Recipe */
    private $recipe;

    public function __construct(Recipe $recipe, $options = [])
    {
        $this->recipe = $recipe;
        parent::__construct($options);
    }

    public function getChefImageUrl() :?string
    {
        if (!$this->recipe->getChef() || !$this->recipe->getChef()->getImage()) {
            return null;
        }

        return $this->recipe->getChef()->getImage()->getUrl("50");
    }

    public function getChefName() :?string
    {
        if (!$this->recipe->getChef()) {
            return null;
        }

        return $this->recipe->getChef()->getName();
    }

    public function getRecipeUrl() :string
    {
        return $this->recipe->getUrl();
    }

    public function getTitle() :string
    {
        return $this->recipe->getTitle();
    }

    public function getImage() :?RecipeImage
    {
        return $this->recipe->getImage();
    }
}
