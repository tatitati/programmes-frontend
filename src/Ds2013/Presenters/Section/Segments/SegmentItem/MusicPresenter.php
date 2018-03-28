<?php
declare(strict_types=1);

namespace App\Ds2013\Presenters\Section\Segments\SegmentItem;

use BBC\ProgrammesPagesService\Domain\Entity\MusicSegment;

class MusicPresenter extends AbstractSegmentItemPresenter
{
    /** @var MusicSegment */
    private $segment;

    public function __construct(MusicSegment $segment, array $options = [])
    {
        parent::__construct($options);
        $this->segment = $segment;
    }

    public function getType(): string
    {
        return 'music';
    }

    public function getTitle(): ?string
    {
        return $this->segment->getTitle();
    }
}
