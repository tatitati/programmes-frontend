<?php
declare(strict_types=1);

namespace App\Ds2013\Presenters\Section\Segments\SegmentItem;

use App\DsShared\Helpers\PlayTranslationsHelper;
use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;

class SpeechPresenter extends AbstractSegmentItemPresenter
{
    /** @var PlayTranslationsHelper */
    private $playTranslationsHelper;

    public function __construct(
        PlayTranslationsHelper $playTranslationsHelper,
        SegmentEvent $segmentEvent,
        array $options = []
    ) {
        parent::__construct($segmentEvent, $options);
        $this->playTranslationsHelper = $playTranslationsHelper;
    }

    public function hasDuration(): bool
    {
        return $this->segmentEvent->isChapter() && $this->segmentEvent->getSegment()->getDuration();
    }

    public function getSynopsis(): string
    {
        return $this->segmentEvent->getSegment()->getSynopses()->getShortestSynopsis();
    }

    public function getDuration(): string
    {
        return $this->playTranslationsHelper->secondsToFormattedDuration($this->segmentEvent->getSegment()->getDuration());
    }

    public function getIdentifyingClass(): string
    {
        // This class is currently just used by test for automation. It's not quite as pointless as it looks.
        return 'segments-list__item--' . $this->segmentEvent->getSegment()->getType();
    }
}
