<?php
declare(strict_types=1);

namespace App\ExternalApi\Ada\Domain;

use BBC\ProgrammesPagesService\Domain\Entity\Image;

/**
 * Ada Provides Classes, which are overarching containers that things may be
 * related to. They come in two types - Categories and Tags.
 *
 * Categories are based on Wikipedia lists e.g. https://en.wikipedia.org/wiki/Category:Fictional_bears
 * Tags are based on Linked Data Platform entities, e.g. https://www.bbc.co.uk/things/3d6bcca8-ba6b-478a-8e50-7cd6e3a5159f
 *
 * In the front end we don't make a distinction between these two types of Class
 */
class AdaClass
{
    /** @var string */
    private $id;

    /** @var string */
    private $title;

    /** @var int */
    private $episodeCount;

    /** @var Image */
    private $image;

    public function __construct(
        string $id,
        string $title,
        int $episodeCount,
        Image $image
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->episodeCount = $episodeCount;
        $this->image = $image;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getEpisodeCount(): int
    {
        return $this->episodeCount;
    }

    public function getImage(): Image
    {
        return $this->image;
    }
}
