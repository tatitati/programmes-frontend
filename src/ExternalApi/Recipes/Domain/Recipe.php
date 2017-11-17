<?php
declare(strict_types=1);

namespace App\ExternalApi\Recipes\Domain;

class Recipe
{
    /** @var string */
    private $id;

    /** @var string */
    private $title;

    /** @var string */
    private $description;

    /** @var Chef|null */
    private $chef;

    /** @var RecipeImage|null */
    private $image;

    public function __construct(
        string $id,
        string $title,
        string $description,
        ?RecipeImage $image = null,
        ?Chef $chef = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->chef = $chef;
        $this->image = $image;
    }

    public function getUrl(): string
    {
        return 'https://www.bbc.co.uk/food/recipes/' . urlencode($this->getId());
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getChef(): ?Chef
    {
        return $this->chef;
    }

    public function getImage(): ?RecipeImage
    {
        return $this->image;
    }
}
