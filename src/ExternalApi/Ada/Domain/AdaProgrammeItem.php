<?php
declare(strict_types=1);

namespace App\ExternalApi\Ada\Domain;

use BBC\ProgrammesPagesService\Domain\Entity\Programme;

class AdaProgrammeItem
{
    /** @var string */
    private $pid;

    /** @var string */
    private $title;

    /** @var int */
    private $programmeItemCount;

    /** @var AdaClass[] */
    private $relatedByClasses;

    /** @var Programme */
    private $programme;

    public function __construct(
        Programme $programme,
        string $pid,
        string $title,
        int $programmeItemCount,
        array $relatedByClasses = null
    ) {
        $this->programme = $programme;
        $this->pid = $pid;
        $this->title = $title;
        $this->programmeItemCount = $programmeItemCount;
        $this->relatedByClasses = $relatedByClasses;
    }

    public function getPid(): string
    {
        return $this->pid;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getProgrammeItemCount(): int
    {
        return $this->programmeItemCount;
    }

    public function getRelatedByClasses(): array
    {
        return $this->relatedByClasses;
    }

    public function getProgramme(): Programme
    {
        return $this->programme;
    }
}
