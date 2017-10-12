<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Map\SubPresenter;

use App\DsAmen\Presenter;
use App\Exception\InvalidOptionException;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Exception;

class OnDemandPresenter extends Presenter
{
    /** @var mixed[] */
    protected $options = [
        'full_width' => false, // The full width of the right hand MAP column
        'show_mini_map' => false,
    ];

    /** @var string */
    private $class = '1/2@gel1b';

    /** @var CollapsedBroadcast|null */
    private $lastOn;

    /** @var ProgrammeContainer */
    private $programmeContainer;

    /** @var Episode|null */
    private $streamableEpisode;

    public function __construct(ProgrammeContainer $programmeContainer, ?Episode $streamableEpisode, ?CollapsedBroadcast $lastOn, $options = [])
    {
        parent::__construct($options);
        $this->lastOn = $lastOn;
        $this->programmeContainer = $programmeContainer;
        $this->streamableEpisode = $streamableEpisode;

        if ($this->getOption('full_width')) {
            $this->class = '1/1';
        }
    }

    public function getImageSizes(): array
    {
        if ($this->options['full_width']) {
            return [768 => 1/3, 1008 => '324px', 1280 => '414px'];
        }
        return [320 => 1/2, 768 => 1/4, 1008 => '242px', 1280 => '310px'];
    }

    public function getDefaultImageSize(): int
    {
        return $this->options['full_width'] ? 324 : 242;
    }

    public function getAllLinkLocation(): string
    {
        return $this->programmeContainer->isRadio() ? 'map_ondemand_all' : 'map_iplayer_all';
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getUpcomingEpisode(): ?Episode
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

        if ($this->lastOnNotAvailableYet()) {
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

    /**
     * This is when a programme has finished broadcasting, but is not available to stream yet.
     * So instead of showing the old streamable episode, we show the just broadcast episode with a coming soon badge
     *
     * @return bool
     */
    public function lastOnNotAvailableYet(): bool
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
        if ($this->getOption('show_mini_map')) {
            return false;
        }

        $item = $this->lastOnNotAvailableYet() ? $this->lastOn->getProgrammeItem() : $this->streamableEpisode;

        if (null === $item) {
            return false;
        }

        // Don't show the image if it's the same as the main image
        return !$this->pidsMatch($this->programmeContainer->getImage()->getPid(), $item->getImage()->getPid());
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

    private function pidsMatch(Pid $firstPid, Pid $secondPid): bool
    {
        return (string) $firstPid === (string) $secondPid;
    }
}
