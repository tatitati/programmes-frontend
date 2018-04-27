<?php
namespace App\Controller\Recipes;

class RecipesController extends AbstractRecipesController
{
    protected function renderRecipes(array $dataForTemplate)
    {
        return $this->renderWithChrome('recipes/show.html.twig', $dataForTemplate);
    }
}
