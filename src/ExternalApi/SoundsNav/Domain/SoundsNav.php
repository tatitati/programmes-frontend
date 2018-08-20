<?php
declare(strict_types = 1);

namespace App\ExternalApi\SoundsNav\Domain;

class SoundsNav
{
    /** @var string */
    private $body;

    /** @var string */
    private $foot;

    /** @var string */
    private $head;

    public function __construct(
        string $head,
        string $body,
        string $foot
    ) {
        $this->head = $head;
        $this->body = $body;
        $this->foot = $foot;
    }

    public function getHead(): string
    {
        return $this->head;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getFoot(): string
    {
        return $this->foot;
    }
}
