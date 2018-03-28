<?php
declare(strict_types=1);

namespace App\Ds2013\Presenters\Section\Segments\SegmentItem;

use BBC\ProgrammesPagesService\Domain\Entity\Segment;

class ChapterPresenter extends AbstractSegmentItemPresenter
{
    /** @var Segment */
    private $segment;

    public function __construct(Segment $segment, array $options = [])
    {
        parent::__construct($options);
        $this->segment = $segment;
    }

    public function getType(): string
    {
        return 'chapter';
    }

    public function getTitle(): ?string
    {
        return $this->segment->getTitle();
    }
}
