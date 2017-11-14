<?php
namespace App\Builders;

use BBC\ProgrammesPagesService\Domain\Entity\RelatedLink;

class RelatedLinkBuilder implements BuilderInterface
{
    /** @var string */
    private $title;

    /** @var string */
    private $uri;

    /** @var string */
    private $shortSynopsis;

    /** @var string */
    private $longestSynopsis;

    /** @var string */
    private $type;

    /** @var bool */
    private $isExternal;

    private function __construct()
    {
        $this->title = 'my title';
        $this->uri = 'https://www.something_not_coming_from_ourcompany.net';
        $this->shortSynopsis = 'my short synopsis';
        $this->longestSynopsis = 'my longest synopsis is a bit long';
        $this->type = 'my type';
        $this->isExternal = true;
    }

    public function withTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }

    public function withUri(string $uri)
    {
        $this->uri = $uri;
        return $this;
    }

    public function withShortSynopsis(string $shortSynopsis)
    {
        $this->shortSynopsis = $shortSynopsis;
        return $this;
    }

    public function withLongestSynopsis(string $longestSynopsis)
    {
        $this->longestSynopsis = $longestSynopsis;
        return $this;
    }

    public function withType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    public function withIsExternal(bool $isExternal)
    {
        $this->isExternal = $isExternal;
        return $this;
    }


    public static function default()
    {
        return new self();
    }

    public function build(): RelatedLink
    {
        return new RelatedLink(
            $this->title,
            $this->uri,
            $this->shortSynopsis,
            $this->longestSynopsis,
            $this->type,
            $this->isExternal
        );
    }
}
