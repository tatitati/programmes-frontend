<?php
namespace App\Controller\Recipes;

class RecipesController extends AbstractRecipesController
{
    protected function renderRecipes(array $dataForTemplate)
    {
        $this->setContextAndPreloadBranding($dataForTemplate['programme']);

        $dataForTemplate['options'] = [
            'srcset' => [
                0 => 1/2,
                1008 => '464px',
            ],
        ];

        return $this->renderWithChrome('recipes/show.html.twig', $dataForTemplate);
    }
}
