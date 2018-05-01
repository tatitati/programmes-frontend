<?php
namespace App\Controller\Recipes;

class RecipesController extends AbstractRecipesController
{
    protected function renderRecipes(array $dataForTemplate)
    {
        $this->setContextAndPreloadBranding($dataForTemplate['programme']);

        return $this->renderWithChrome('recipes/show.html.twig', $dataForTemplate);
    }
}
