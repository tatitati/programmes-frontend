<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Domain\Recipe;

use App\DsAmen\Presenter;
use App\ExternalApi\Recipes\Domain\Chef;
use App\ExternalApi\Recipes\Domain\Recipe;

class RecipePresenter extends Presenter
{
    /** @var Recipe */
    private $recipe;

    protected $options = [
        'h_tag' => 'h4',
        'title_size' => 'gel-pica-bold',
        'img_default_width' => 320,
        'img_sizes' => [],
        'srcsets' => [160, 235, 280, 320, 466, 608],
        'img_is_lazy_loaded' => true,
        'media_variant' => 'media--column media--card',
        'cta_class' => 'cta--dark',
        'media_panel_class' => '1/1',
        'branding_name' => 'subtle',
        'link_location_prefix' => 'programmes_recipe_',
    ];

    public function __construct(Recipe $recipe, array $options = [])
    {
        parent::__construct($options);
        $this->recipe = $recipe;
    }

    public function getBrandingBoxClass(): string
    {
        if (!$this->getOption('branding_name')) {
            return '';
        }

        return 'br-box-' . $this->getOption('branding_name');
    }

    public function getTextBrandingClass(): string
    {
        if (!$this->getOption('branding_name')) {
            return '';
        }

        return 'br-' . $this->getOption('branding_name') . '-text-ontext';
    }

    public function getRecipe(): Recipe
    {
        return $this->recipe;
    }

    public function getChef(): ?Chef
    {
        return $this->recipe->getChef();
    }
}
