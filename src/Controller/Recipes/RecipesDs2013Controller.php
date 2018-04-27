<?php
namespace App\Controller\Recipes;

class RecipesDs2013Controller extends AbstractRecipesController
{
    protected function renderRecipes(array $dataForTemplate)
    {
        return $this->render('recipes/show.2013inc.html.twig', $dataForTemplate, $this->response());
    }
}
