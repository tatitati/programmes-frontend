<?php
declare(strict_types=1);

namespace App\Ds2013\Presenters\Section\Segments\SegmentItem;

use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;

class GroupPresenter extends AbstractSegmentItemPresenter
{
    /** @var AbstractSegmentItemPresenter[] */
    private $presenters;

    public function __construct(SegmentEvent $segmentEvent, array $presenters, array $options)
    {
        parent::__construct($segmentEvent, $options);
        $this->presenters = $presenters;
    }

    /** @return AbstractSegmentItemPresenter[] */
    public function getPresenters(): array
    {
        return $this->presenters;
    }

    public function getTitle(): ?string
    {
        return $this->segmentEvent->getTitle();
    }
}
