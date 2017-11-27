<?php
declare(strict_types = 1);
namespace App\Builders;

use BBC\ProgrammesPagesService\Domain\Entity\Format;
use BBC\ProgrammesPagesService\Domain\Entity\Genre;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\MasterBrand;
use BBC\ProgrammesPagesService\Domain\Entity\Options;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\ValueObject\PartialDate;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use DateTimeImmutable;

class AbstractProgrammeItemBuilder
{
    /** @var int[] */
    protected $dbAncestryIds;

    /** @var Pid */
    protected $pid;

    /** @var string */
    protected $title;

    /** @var string */
    protected $searchTitle;

    /** @var Synopses  */
    protected $synopses;

    /** @var Image */
    protected $image;

    /** @var int */
    protected $promotionsCount;

    /** @var int */
    protected $relatedLinksCount;

    /** @var bool */
    protected $hasSupportingContent;

    /** @var bool */
    protected $isStreamable;

    /** @var bool */
    protected $isStreamableAlternate;

    /** @var int */
    protected $contributionsCount;

    /**
     * @see \BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum
     * @var string
     */
    protected $mediaType;

    /** @var int */
    protected $segmentEventCount;

    /** @var int */
    protected $aggregatedGalleriesCount;

    /** @var Options */
    protected $options;

    /** @var Programme|null */
    protected $parent;

    /** @var int|null */
    protected $position;

    /** @var MasterBrand|null */
    protected $masterBrand;

    /** @var Genre[]|null */
    protected $genres;

    /** @var Format[]|null */
    protected $formats;

    /** @var DateTimeImmutable|null */
    protected $firstBroadcastDate;

    /** @var PartialDate|null */
    protected $releaseDate;

    /** @var int|null */
    protected $duration;

    /** @var DateTimeImmutable|null */
    protected $streamableFrom;

    /** @var DateTimeImmutable|null */
    protected $streamableUntil;


    public function withDbAncestryIds(array $dbAncestries)
    {
        $this->dbAncestryIds = $dbAncestries;
        return $this;
    }

    public function withPid(string $pid)
    {
        $this->pid = new Pid($pid);
        return $this;
    }

    public function withTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }

    public function withSearchTitle(string $searchTitle)
    {
        $this->searchTitle = $searchTitle;
        return $this;
    }

    public function withSynopses(Synopses $synopses)
    {
        $this->synopses = $synopses;
        return $this;
    }

    public function withImage(Image $image)
    {
        $this->image = $image;
        return $this;
    }

    public function withPromotionsCount(int $promotionsCount)
    {
        $this->promotionsCount = $promotionsCount;
        return $this;
    }

    public function withRelatedLinksCount(int $relatedLinksCount)
    {
        $this->relatedLinksCount = $relatedLinksCount;
        return $this;
    }

    public function witHasSupportingContent(bool $hasSupportingContent)
    {
        $this->hasSupportingContent = $hasSupportingContent;
        return $this;
    }

    public function withIsStreamable(bool $isStremable)
    {
        $this->isStreamable = $isStremable;
        return $this;
    }

    public function withIsStreamableAlternate(bool $isStreamableAlternate)
    {
        $this->isStreamableAlternate = $isStreamableAlternate;
        return $this;
    }

    public function withContributionsCount(int $contributionsCount)
    {
        $this->contributionsCount = $contributionsCount;
        return $this;
    }

    /**
     * @see \BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum
     */
    public function withMediaType(string $mediatype)
    {
        $this->mediaType = $mediatype;
        return $this;
    }

    public function withSegmentEventCount(int $segmentEventCount)
    {
        $this->segmentEventCount = $segmentEventCount;
        return $this;
    }

    public function withAggregatedGalleriesCount(int $aggregatedGalleriesCount)
    {
        $this->aggregatedGalleriesCount = $aggregatedGalleriesCount;
        return $this;
    }

    public function withOptions(Options $options)
    {
        $this->options = $options;
        return $this;
    }

    public function withParent(?Programme $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    public function withPosition(?int  $position)
    {
        $this->position = $position;
        return $this;
    }

    public function withMasterBrand(?MasterBrand $masterbrand)
    {
        $this->masterBrand = $masterbrand;
        return $this;
    }

    public function withGenres(?array $genres)
    {
        $this->genres = $genres;
        return $this;
    }

    public function withFormats(?array $formats)
    {
        $this->formats = $formats;
        return $this;
    }

    public function withFirstBroadcastDate(?DateTimeImmutable $firstBroadcastDate)
    {
        $this->firstBroadcastDate = $firstBroadcastDate;
        return $this;
    }

    public function withReleaseDate(?PartialDate $releaseDate)
    {
        $this->releaseDate = $releaseDate;
        return $this;
    }

    public function withDuration(?int $duration)
    {
        $this->duration = $duration;
        return $this;
    }

    public function withStreamableFrom(?DateTimeImmutable $streamableFrom)
    {
        $this->streamableFrom = $streamableFrom;
        return $this;
    }

    public function withStremableUntil(?DateTimeImmutable $streamableUntil)
    {
        $this->streamableUntil = $streamableUntil;
        return $this;
    }
}
