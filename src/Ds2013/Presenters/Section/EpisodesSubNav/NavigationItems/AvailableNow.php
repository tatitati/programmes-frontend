<?php

namespace App\Ds2013\Presenters\Section\EpisodesSubNav\NavigationItems;

use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;

class AvailableNow extends NavigationItemWithCount
{
    /** @var int */
    private $availableEpisodeCount;

    public function __construct(Pid $pid, bool $selected, int $availableEpisodeCount)
    {
        parent::__construct($pid, $selected);

        $this->availableEpisodeCount = $availableEpisodeCount;
    }

    public function getTranslationString(): string
    {
        return 'available_now';
    }

    public function getRoute(): string
    {
        return 'programme_episodes_player';
    }

    public function getCount(): int
    {
        return $this->availableEpisodeCount;
    }
}
