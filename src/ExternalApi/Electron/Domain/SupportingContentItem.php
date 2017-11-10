<?php
declare(strict_types = 1);

namespace App\ExternalApi\Electron\Domain;

use BBC\ProgrammesPagesService\Domain\Entity\Image;

class SupportingContentItem
{
    /** @var string */
    private $title;

    /** @var string */
    private $htmlContent;

    /** @var Image|null */
    private $image;

    public function __construct(string $title, string $htmlContent, ?Image $image)
    {
        $this->title = $title;
        $this->htmlContent = $htmlContent;
        $this->image = $image;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getHtmlContent(): string
    {
        return $this->htmlContent;
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }
}
