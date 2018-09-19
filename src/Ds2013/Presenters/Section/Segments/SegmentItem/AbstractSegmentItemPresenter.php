<?php
declare(strict_types=1);

namespace App\Ds2013\Presenters\Section\Segments\SegmentItem;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;

abstract class AbstractSegmentItemPresenter extends Presenter
{
    /** @var SegmentEvent */
    protected $segmentEvent;

    protected $options = [
        'moreless_class' => '',
        'h_tag' => 'h3',
    ];

    public function __construct(SegmentEvent $segmentEvent, array $options = [])
    {
        parent::__construct($options);
        $this->segmentEvent = $segmentEvent;
    }

    public function getTitle(): ?string
    {
        if (empty($this->segmentEvent->getSegment()->getTitle())) {
            return 'Untitled';
        }

        return $this->segmentEvent->getSegment()->getTitle();
    }

    public function getSegmentEvent(): SegmentEvent
    {
        return $this->segmentEvent;
    }

    public function getIdentifyingClass(): string
    {
        // This class is currently just used by test for automation. It's not quite as pointless as it looks.
        return 'segments-list__item--' . $this->segmentEvent->getSegment()->getType();
    }
}
