<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Map\SubPresenter;

use App\DsAmen\Organism\Map\SubPresenter\Traits\RightColumnImageSizeTrait;
use App\DsAmen\Presenter;
use App\Exception\InvalidOptionException;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use Exception;

class OnDemandPresenter extends Presenter
{
    use RightColumnImageSizeTrait;

    /** @var mixed[] */
    protected $options = [
        'full_width' => false, // The full width of the right hand MAP column
        'show_mini_map' => false,
    ];

    /** @var string */
    private $class = '1/2@gel1b';

    /**
     * An upcoming episode is an Episode that will be broadcast and streamable in the future
     * @var bool
     */
    private $hasUpcomingEpisode;

    /**
     * The last on is the CollapsedBroadcast for the last thing that was on.
     * Its associated episode may or may not be streamable yet
     * @var CollapsedBroadcast|null
     */
    private $lastOn;

    /** @var ProgrammeContainer */
    private $programmeContainer;

    /**
     * An streamable episode is an Episode that is streamable right now
     * @var Episode|null
     */
    private $streamableEpisode;

    public function __construct(ProgrammeContainer $programmeContainer, ?Episode $streamableEpisode, bool $hasUpcomingEpisode, ?CollapsedBroadcast $lastOn, $options = [])
    {
        parent::__construct($options);
        $this->lastOn = $lastOn;
        $this->programmeContainer = $programmeContainer;
        $this->streamableEpisode = $streamableEpisode;
        $this->hasUpcomingEpisode = $hasUpcomingEpisode;
        if ($this->getOption('full_width')) {
            $this->class = '1/1';
        }
    }

    public function getAllLinkLocation(): string
    {
        return $this->programmeContainer->isRadio() ? 'map_ondemand_all' : 'map_iplayer_all';
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getPendingEpisode(): ?Episode
    {
        if ($this->lastOn === null) {
            return null;
        }
        return $this->lastOn->getProgrammeItem();
    }

    public function getBadgeTranslationString(): string
    {
        if ($this->streamableEpisode === null && $this->lastOn === null) {
            throw new Exception('Streamable or LastOn must be set in order to call ' . __FUNCTION__);
        }
        if ($this->episodeIsPending()) {
            return 'coming_soon';
        }
        // Coming soon can be displayed on Radio and TV pages.
        // New and New Series should only display on TV pages.
        if (!$this->programmeContainer->isTv()) {
            return '';
        }
        // If the parent is the TLEO (e.g. Eastenders) we don't want to show a new badge for each episode
        // Otherwise (e.g. Mongrels) the parent will be a series, which we do want badges for each episode.
        if (!$this->streamableEpisode->getParent() || $this->streamableEpisode->getParent()->isTleo()) {
            return '';
        }
        if ($this->streamableEpisode->getFirstBroadcastDate() === null || !$this->streamableEpisode->getFirstBroadcastDate()->wasWithinLast('7 days')) {
            return '';
        }
        return $this->streamableEpisode->getPosition() === 1 ? 'new_series' : 'new';
    }

    public function getProgramme(): ProgrammeContainer
    {
        return $this->programmeContainer;
    }

    public function getStreamableEpisode(): ?Episode
    {
        return $this->streamableEpisode;
    }

    public function getTitleTranslationString(): string
    {
        return $this->programmeContainer->isRadio() ? 'on_demand' : 'available_on_iplayer_short';
    }

    public function hasUpcomingEpisode(): bool
    {
        return $this->hasUpcomingEpisode;
    }

    /**
     * This is when a programme has finished broadcasting, but is not available to stream yet.
     * So instead of showing the old streamable episode, we show the just broadcast episode with a coming soon badge
     *
     * @return bool
     */
    public function episodeIsPending(): bool
    {
        if (!$this->lastOn || !$this->lastOn->getProgrammeItem()) {
            return false;
        }
        $hasFutureAvailablity = !($this->lastOn->getProgrammeItem()->getStreamableFrom() === null || $this->lastOn->getProgrammeItem()->isStreamable());
        if (!$hasFutureAvailablity) {
            return false;
        }
        // If the broadcast was over 7 days ago, but still isn't streamable, revert back to the previous episode
        return $this->lastOn->getStartAt()->wasWithinLast('7 days');
    }

    public function shouldShowImage(): bool
    {
        return !$this->getOption('show_mini_map');
    }

    public function showMiniMap(): bool
    {
        return $this->getOption('show_mini_map');
    }

    protected function validateOptions(array $options): void
    {
        if (!is_bool($options['show_mini_map'])) {
            throw new InvalidOptionException('show_mini_map option must be a boolean');
        }
        if (!is_bool($options['full_width'])) {
            throw new InvalidOptionException('full_width option must be a boolean');
        }
    }
}
