<?php
declare(strict_types = 1);
namespace App\Builders;

use BBC\ProgrammesPagesService\Domain\Entity\Episode;
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

class EpisodeBuilder implements BuilderInterface
{
    /** @var int[] */
    private $dbAncestryIds;

    /** @var Pid */
    private $pid;

    /** @var string */
    private $title;

    /** @var string */
    private $searchTitle;

    /** @var Synopses  */
    private $synopses;

    /** @var Image */
    private $image;

    /** @var int */
    private $promotionsCount;

    /** @var int */
    private $relatedLinksCount;

    /** @var bool */
    private $hasSupportingContent;

    /** @var bool */
    private $isStreamable;

    /** @var bool */
    private $isStreamableAlternate;

    /** @var int */
    private $contributionsCount;

    /**
     * @see \BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum
     * @var string
     */
    private $mediaType;

    /** @var int */
    private $segmentEventCount;

    /** @var int */
    private $aggregatedGalleriesCount;

    /** @var Options */
    private $options;

    /** @var Programme|null */
    private $parent;

    /** @var int */
    private $position;

    /** @var MasterBrand|null */
    private $masterBrand;

    /** @var Genre[] */
    private $genres;

    /** @var Format[] */
    private $formats;

    /** @var DateTimeImmutable */
    private $firstBroadcastDate;

    /** @var PartialDate */
    private $releaseDate;

    /** @var int */
    private $duration;

    /** @var DateTimeImmutable */
    private $streamableFrom;

    /** @var DateTimeImmutable */
    private $streamableUntil;

    /** @var int */
    private $aggregatedBroadcastsCount;

    /** @var int */
    private $availableClipsCount;

    private function __construct()
    {
        $this->dbAncestryIds = [1212];
        $this->pid = new Pid('d00744wz');
        $this->title = 'my episode title';
        $this->searchTitle = 'my search episode title';
        $this->synopses = new Synopses('My short synopsis', 'my medium synopsis', 'my long synopsis');
        $this->image = ImageBuilder::default()->build();
        $this->promotionsCount = 10;
        $this->relatedLinksCount = 5;
        $this->hasSupportingContent = false;
        $this->isStreamable = true;
        $this->isStreamableAlternate = true;
        $this->contributionsCount = 2;
        $this->mediaType = 'audio';
        $this->segmentEventCount = 19;
        $this->aggregatedGalleriesCount = 2;
        $this->options = new Options();
        // optionals
        $this->parent = null;
        $this->position = 3;
        $this->masterBrand = null;
        $this->genres = [new Genre([2], 'other_id', 'Other Title', 'other_url_key', null)];
        $this->formats = [new Format([4], 'other_format_id', 'other_format_title', 'other_format_url_key')];
        $this->firstBroadcastDate = new DateTimeImmutable('2012-06-05');
        $this->releaseDate =  new PartialDate(2000, 10, 5);
        $this->duration = 23;
        $this->streamableFrom = new DateTimeImmutable('2012-06-03');
        $this->streamableUntil = new DateTimeImmutable('2100-06-03');
        $this->aggregatedBroadcastsCount = 10;
        $this->availableClipsCount = 14;
    }

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

    public function withParent(Programme $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    public function withPosition(int  $position)
    {
        $this->position = $position;
        return $this;
    }

    public function withMasterBrand(MasterBrand $masterbrand)
    {
        $this->masterBrand = $masterbrand;
        return $this;
    }

    /**
     * @param Genre[] $genres
     */
    public function withGenres(array $genres)
    {
        $this->genres = $genres;
        return $this;
    }

    /**
     * @param Format[] $formats
     */
    public function withFormats(array $formats)
    {
        $this->formats = $formats;
        return $this;
    }

    public function withFirstBroadcastDate(DateTimeImmutable $firstBroadcastDate)
    {
        $this->firstBroadcastDate = $firstBroadcastDate;
        return $this;
    }

    public function withReleaseDate(PartialDate $releaseDate)
    {
        $this->releaseDate = $releaseDate;
        return $this;
    }

    public function withDuration(int $duration)
    {
        $this->duration = $duration;
        return $this;
    }

    public function withStreamableFrom(DateTimeImmutable $streamableFrom)
    {
        $this->streamableFrom = $streamableFrom;
        return $this;
    }

    public function withStremableUntil(DateTimeImmutable $streamableUntil)
    {
        $this->streamableUntil = $streamableUntil;
        return $this;
    }

    public function withAggregatedBroadcastsCount(int $aggregatedBroadcastsCount)
    {
        $this->aggregatedBroadcastsCount = $aggregatedBroadcastsCount;
        return $this;
    }

    public function withAvailableClipsCount(int $availableClipsCount)
    {
        $this->availableClipsCount = $availableClipsCount;
        return $this;
    }

    public static function default()
    {
        return new self();
    }

    public function build(): Episode
    {
        return new Episode(
            $this->dbAncestryIds,
            $this->pid,
            $this->title,
            $this->searchTitle,
            $this->synopses,
            $this->image,
            $this->promotionsCount,
            $this->relatedLinksCount,
            $this->hasSupportingContent,
            $this->isStreamable,
            $this->isStreamableAlternate,
            $this->contributionsCount,
            $this->mediaType,
            $this->segmentEventCount,
            $this->aggregatedBroadcastsCount,
            $this->availableClipsCount,
            $this->aggregatedGalleriesCount,
            $this->options,
            // optionals
            $this->parent,
            $this->position,
            $this->masterBrand,
            $this->genres,
            $this->formats,
            $this->firstBroadcastDate,
            $this->releaseDate,
            $this->duration,
            $this->streamableFrom,
            $this->streamableUntil
        );
    }
}
