<?php
declare(strict_types=1);

namespace App\ExternalApi\Recipes\Domain;

class RecipesApiResult
{
    /** @var int */
    private $total;

    /** @var Recipe[] */
    private $recipes;

    public function __construct(array $recipes, int $total)
    {
        $this->total = $total;
        $this->recipes = $recipes;
    }

    /** @return Recipe[] */
    public function getRecipes(): array
    {
        return $this->recipes;
    }

    public function getTotal(): int
    {
        return $this->total;
    }
}
