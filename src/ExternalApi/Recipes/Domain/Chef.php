<?php
declare(strict_types=1);

namespace App\ExternalApi\Recipes\Domain;

class Chef
{
    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var ChefImage|null */
    private $image;

    public function __construct(string $id, string $name, ?ChefImage $image)
    {
        $this->id = $id;
        $this->name = $name;
        $this->image = $image;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getImage(): ?ChefImage
    {
        return $this->image;
    }
}
