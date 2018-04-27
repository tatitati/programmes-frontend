<?php
namespace App\Controller\Recipes;

class RecipesAmenController extends AbstractRecipesController
{
    protected function renderRecipes(array $dataForTemplate)
    {
        return $this->render('recipes/show.ameninc.html.twig', $dataForTemplate, $this->response());
    }
}
