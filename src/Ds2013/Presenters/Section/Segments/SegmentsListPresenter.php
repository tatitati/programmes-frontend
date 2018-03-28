<?php
declare(strict_types=1);

namespace App\Ds2013\Presenters\Section\Segments;

use App\Ds2013\Presenter;
use App\Ds2013\Presenters\Section\Segments\SegmentItem\AbstractSegmentItemPresenter;
use App\Ds2013\Presenters\Section\Segments\SegmentItem\ChapterPresenter;
use App\Ds2013\Presenters\Section\Segments\SegmentItem\MusicPresenter;
use App\Ds2013\Presenters\Section\Segments\SegmentItem\GroupPresenter;
use App\Ds2013\Presenters\Section\Segments\SegmentItem\SpeechPresenter;
use App\DsShared\Helpers\LiveBroadcastHelper;
use App\Exception\InvalidOptionException;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\MusicSegment;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;

class SegmentsListPresenter extends Presenter
{
    /** @var SegmentEvent[] */
    private $segmentEvents;

    /** @var LiveBroadcastHelper */
    private $liveBroadcastHelper;

    /** @var ProgrammeItem */
    private $context;

    /** @var CollapsedBroadcast|null */
    private $collapsedBroadcast;

    /** @var bool|null */
    private $isLive = null;

    /**
     * Has the list of segment events been reversed?
     *
     * @var bool
     */
    private $isReversed = false;

    protected $options = [
        'h_tag_level' => 2,
    ];

    /**
     * @param LiveBroadcastHelper $liveBroadcastHelper
     * @param ProgrammeItem $context
     * @param SegmentEvent[] $segmentEvents
     * @param CollapsedBroadcast|null $upcoming
     * @param CollapsedBroadcast|null $lastOn
     * @param array $options
     */
    public function __construct(
        LiveBroadcastHelper $liveBroadcastHelper,
        ProgrammeItem $context,
        array $segmentEvents,
        ?CollapsedBroadcast $upcoming,
        ?CollapsedBroadcast $lastOn,
        array $options = []
    ) {
        parent::__construct($options);
        $this->liveBroadcastHelper = $liveBroadcastHelper;
        $this->context = $context;
        $this->collapsedBroadcast = $upcoming ?? $lastOn;
        $this->segmentEvents = $segmentEvents;
    }

    public function getTitle(): string
    {
        $hasChapterSegments = false;
        $hasMusicSegments = false;

        foreach ($this->segmentEvents as $segmentEvent) {
            if ($segmentEvent->getSegment() instanceof MusicSegment) {
                $hasMusicSegments = true;
            } elseif ($segmentEvent->getSegment()->getType() === 'chapter') {
                $hasChapterSegments = true;
            }
        }

        if ($hasMusicSegments) {
            if ($hasChapterSegments) {
                return 'music_and_featured';
            }

            return 'music_played';
        }

        if ($hasChapterSegments) {
            return 'chapters';
        }

        return 'featured';
    }

    public function getSegmentEvents(): array
    {
        return $this->segmentEvents;
    }

    public function getMorelessClass(): string
    {
        return $this->hasMoreless() ? 'ml@bpb1' : '';
    }

    public function getHeadingTag(): string
    {
        return 'h' . $this->getOption('h_tag_level');
    }

    public function hasMoreless(): bool
    {
        return count($this->segmentEvents) >= 6;
    }

    public function hasTimingIntro(): bool
    {
        return !($this->context instanceof Clip) && $this->context->getOption('show_tracklist_timings');
    }

    public function getTimingIntroTranslationString(): string
    {
        if ($this->collapsedBroadcast && $this->collapsedBroadcast->getStartAt()->isFuture()) {
            return 'timings_start_of_day';
        }

        return 'timings_start_of_programme';
    }

    /** @return AbstractSegmentItemPresenter[][] */
    public function getSegmentItemsPresenters(): array
    {
        $this->segmentEvents = $this->filterSegmentEvents();
        if (!$this->segmentEvents) {
            return [];
        }

        $presenters = [];

        $segmentItems = [];
        $previousTitle = reset($this->segmentEvents)->getTitle();
        $start = 0;

        // On groups:
        //
        // This is a VERY naughty abuse of the data model.
        //
        // Take a look https://www.bbc.co.uk/programmes/p005xm30
        // See how the segments are neatly split into different sections, with a 'MUSIC PLAYED'
        // title before each section? That's done by using Segment Event titles.
        //
        // Usually, Segment Events do not have titles, because we don't care about them.
        // We care about the Segments titles, which contain the segment information rather than the timing
        // information within the programme, which is what the Segment Event object does.
        //
        // So, as Segment Event titles are not something we use for anything else
        // it's used as markers for the start and end of a group, as well as their titles
        //
        // Therefore, a group is defined as a series of consecutive Segment Events that share
        // the same non-empty title. Note that this series can contain a single element. As
        // long the Segment Event has a non-empty title, it belongs to a group

        // We use the index and call it 'relative offset' here because we could
        // have reversed the array in case of music segments for live programme debuts.
        foreach ($this->segmentEvents as $relativeOffset => $segmentEvent) {
            // null, empty or different titles mean new group
            if (empty($previousTitle) || $segmentEvent->getTitle() != $previousTitle) {
                if ($segmentItems) {
                    $presenters[] = $this->createSegmentItem($segmentItems, $start);
                }

                $start = $relativeOffset;
                $segmentItems = [];
            }

            $segmentItems[] = $segmentEvent;
            $previousTitle = $segmentEvent->getTitle();
        }

        // add the last group that didn't get added in the loop (if it's not empty)
        if ($segmentItems) {
            $presenters[] = $this->createSegmentItem($segmentItems, $start);
        }

        return $presenters;
    }

    protected function validateOptions(array $options): void
    {
        if (!is_int($options['h_tag_level']) || $options['h_tag_level'] < 1) {
            throw new InvalidOptionException('h_tag_level has to be a positive integer');
        }
    }

    private function filterSegmentEvents(): array
    {
        if ($this->context->getOption('show_tracklist_inadvance') ||
            !$this->collapsedBroadcast ||
            $this->collapsedBroadcast->isRepeat() ||
            !$this->isLive($this->collapsedBroadcast)
        ) {
            return $this->segmentEvents;
        }

        // if the programme item is currently being broadcast for the first time, filter to only the segment events that
        // have already started
        $filteredSegmentEvents = [];
        $currentOffset = $this->collapsedBroadcast->getStartAt()->diffInSeconds(ApplicationTime::getTime(), false);

        foreach ($this->segmentEvents as $segmentEvent) {
            // music segments that have offsets get reversed
            // check if we're already reversing to avoid checking all conditions again
            if (!$this->isReversed && $segmentEvent->getSegment() instanceof MusicSegment && !is_null($segmentEvent->getOffset())) {
                $this->isReversed = true;
            }

            // things within the offset range, or that don't have offsets, get displayed
            if (!$segmentEvent->getOffset() || $segmentEvent->getOffset() <= $currentOffset) {
                $filteredSegmentEvents[] = $segmentEvent;
            }
        }

        if ($this->isReversed) {
            $filteredSegmentEvents = array_reverse($filteredSegmentEvents);
        }

        return $filteredSegmentEvents;
    }

    private function createSegmentItem(array $segmentEvents, int $start): AbstractSegmentItemPresenter
    {
        $current = $start;
        $presenters = [];

        // If the segment event title is non-empty, we're dealing with a group
        $isGroup = !empty(reset($segmentEvents)->getTitle());

        // If a segment item is part of a group, the h tag has to be one level deeper
        $hTag = 'h' . ($this->getOption('h_tag_level') + ($isGroup ? 2 : 1));

        /** @var SegmentEvent $segmentEvent */
        foreach ($segmentEvents as $segmentEvent) {
            $options = ['h_tag' => $hTag];
            // If there are more than 6 segment events and we're past the fourth one, we start hiding things
            if ($this->hasMoreless() && $current > 3) {
                $options['moreless_class'] = 'ml__hidden';
            }

            if ($segmentEvent->getSegment() instanceof MusicSegment) {
                $presenters[] = new MusicPresenter($segmentEvent->getSegment(), $options);
            } elseif ($segmentEvent->isChapter() &&
                $segmentEvent->getOffset() &&
                ($this->context->isStreamable() || $this->context->isDownloadable()) &&
                !$this->isLive
            ) {
                $presenters[] = new ChapterPresenter($segmentEvent->getSegment(), $options);
            } else {
                $presenters[] = new SpeechPresenter($segmentEvent->getSegment(), $options);
            }

            $current += 1;
        }

        if ($isGroup) {
            $options = ['h_tag' => 'h' . ($this->getOption('h_tag_level') + 1)];
            if ($this->hasMoreless() && $start > 3) {
                $options['moreless_class'] = 'ml__hidden';
            }

            return new GroupPresenter(reset($segmentEvents)->getTitle(), $presenters, $options);
        }

        return reset($presenters);
    }

    private function isLive(?CollapsedBroadcast $collapsedBroadcast): bool
    {
        if (is_null($this->isLive)) {
            $this->isLive = $collapsedBroadcast && $this->liveBroadcastHelper->isOnNowIsh($collapsedBroadcast, true);
        }

        return $this->isLive;
    }
}
