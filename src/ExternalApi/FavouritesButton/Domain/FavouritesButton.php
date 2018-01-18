<?php

namespace App\ExternalApi\FavouritesButton\Domain;

class FavouritesButton
{
    private $head;

    private $script;

    private $bodyLast;

    public function __construct(string $head, string $script, string $bodyLast)
    {
        $this->head = $head;
        $this->script = $script;
        $this->bodyLast = $bodyLast;
    }

    public function getHead(): string
    {
        return $this->head;
    }

    public function getScript(): string
    {
        return $this->script;
    }

    public function getBodyLast(): string
    {
        return $this->bodyLast;
    }
}
