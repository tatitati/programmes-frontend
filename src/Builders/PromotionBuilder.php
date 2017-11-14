<?php
declare(strict_types = 1);
namespace App\Builders;

use BBC\ProgrammesPagesService\Domain\Entity\PromotableInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Promotion;
use BBC\ProgrammesPagesService\Domain\Entity\RelatedLink;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;

class PromotionBuilder implements BuilderInterface
{
    /** @var Pid */
    private $pid;

    /** @var PromotableInterface */
    private $promotedEntity;

    /** @var Synopses */
    private $synopses;

    /** @var string */
    private $title;

    /** @var string */
    private $url;

    /** @var int */
    private $weighting;

    /** @var bool */
    private $isSuperPromotion;

    /** @var RelatedLink[]|null */
    private $relatedLinks;

    private function __construct()
    {
        $this->pid = new Pid('b00744wz');
        $this->promotedEntity = ImageBuilder::default()->build();
        $this->synopses = new Synopses('My short synopsis', 'my medium no too much long synopsis', 'my very long and extremly ever end synopsis');
        $this->title = 'MY TITLE';
        $this->url = 'https://www.something_not_coming_from_bbc.net';
        $this->weighting = 2;
        $this->isSuperPromotion = false;
        $this->relatedLinks = [
            // link external to BBC
            RelatedLinkBuilder::default()->withUri('http://something_that_is_not_bbc.net')->build(),
            // link internal to BBC
            RelatedLinkBuilder::default()->withUri('http://bbc.co.uk/something')->build(),
        ];
    }

    public function withPid(string $pid)
    {
        $this->pid = new Pid($pid);
        return $this;
    }

    public function withPromotedEntity(PromotableInterface $promotedItem)
    {
        $this->promotedEntity = $promotedItem;
        return $this;
    }

    public function withTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }

    public function withSynopses(synopses $synopses)
    {
        $this->synopses = $synopses;
        return $this;
    }

    public function withUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function withWeighting(int $weighting)
    {
        $this->weighting = $weighting;
        return $this;
    }

    public function withIsSuperPromotion(bool $isSuperpromotion)
    {
        $this->isSuperPromotion = $isSuperpromotion;
        return $this;
    }

    public function withRelatedLinks(array $relatedLinks)
    {
        $this->relatedLinks = $relatedLinks;
        return $this;
    }

    public static function default()
    {
        return new self();
    }

    public function build(): Promotion
    {
        return new Promotion(
            $this->pid,
            $this->promotedEntity,
            $this->title,
            $this->synopses,
            $this->url,
            $this->weighting,
            $this->isSuperPromotion,
            $this->relatedLinks
        );
    }
}
