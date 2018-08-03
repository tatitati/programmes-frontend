<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Domain;

class KeyFact
{
    /** @var string */
    private $answer;

    /** @var string */
    private $title;

    /** @var null|string */
    private $url;

    public function __construct(string $title, string $answer, ?string $url)
    {
        $this->answer = $answer;
        $this->title = $title;
        $this->url = $url;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
}
