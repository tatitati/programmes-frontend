<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Map\SubPresenter;

use App\DsAmen\Presenter;
use App\Exception\InvalidOptionException;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\Series;

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

    /** @var Episode|null */
    private $upcomingEpisode;

    public function __construct(ProgrammeContainer $programmeContainer, ?Episode $streamableEpisode, ?Episode $upcomingEpisode, ?CollapsedBroadcast $lastOn, $options = [])
    {
        parent::__construct($options);
        $this->lastOn = $lastOn;
        $this->programmeContainer = $programmeContainer;
        $this->streamableEpisode = $streamableEpisode;
        $this->upcomingEpisode = $upcomingEpisode;

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

    public function getUpcomingEpisode(): ?Episode
    {
        return $this->upcomingEpisode;
    }

    public function getBadgeTranslationString(): string
    {
        if ($this->streamableEpisode === null) {
            return $this->upcomingEpisode->getStreamableFrom()->isFuture() ? 'coming_soon' : '';
        }

        // Coming soon can be displayed on Radio and TV pages.
        // New and New Series should only display on TV pages.
        if (!$this->programmeContainer->isTv()) {
            return '';
        }

        if (!$this->streamableEpisode->getParent() instanceof Series) {
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

    public function justMissed(): bool
    {
        if (!$this->lastOn) {
            return false;
        }

        if ($this->streamableEpisode && (string) $this->streamableEpisode->getPid() === (string) $this->lastOn->getProgrammeItem()->getPid()) {
            return false;
        }

        $sevenDaysAgo = ApplicationTime::getTime()->subDays(7);
        return $this->lastOn->getStartAt()->lt($sevenDaysAgo) && $this->upcomingEpisode && !$this->streamableEpisode;
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
