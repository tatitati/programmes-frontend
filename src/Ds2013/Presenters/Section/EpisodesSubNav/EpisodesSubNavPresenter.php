<?php

namespace App\Ds2013\Presenters\Section\EpisodesSubNav;

use App\Ds2013\Presenter;
use App\Ds2013\Presenters\Section\EpisodesSubNav\NavigationItems\All;
use App\Ds2013\Presenters\Section\EpisodesSubNav\NavigationItems\AvailableNow;
use App\Ds2013\Presenters\Section\EpisodesSubNav\NavigationItems\ByDate;
use App\Ds2013\Presenters\Section\EpisodesSubNav\NavigationItems\NavigationItem;
use App\Ds2013\Presenters\Section\EpisodesSubNav\NavigationItems\NextOn;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;

class EpisodesSubNavPresenter extends Presenter
{
    /** @var int */
    private $availableEpisodeCount;

    /** @var string */
    private $currentRoute;

    /** @var bool */
    private $hasBroadcasts;

    /** @var bool */
    private $isDomestic;

    /** @var Pid */
    private $pid;

    /** @var int */
    private $upcomingBroadcastCount;

    public function __construct(string $currentRoute, bool $isDomestic, bool $hasBroadcasts, int $availableEpisodeCount, Pid $pid, int $upcomingBroadcastCount, array $options = [])
    {
        parent::__construct($options);

        $this->availableEpisodeCount = $availableEpisodeCount;
        $this->hasBroadcasts = $hasBroadcasts;
        $this->currentRoute = $currentRoute;
        $this->isDomestic = $isDomestic;
        $this->pid = $pid;
        $this->upcomingBroadcastCount = $upcomingBroadcastCount;
    }

    /**
     * @return NavigationItem[]
     */
    public function getItems(): array
    {
        $items = [];
        $items[] = new All($this->pid, 'programme_episodes_guide' === $this->currentRoute);
        if ($this->hasBroadcasts) {
            $items[] = new ByDate($this->pid, 'programme_broadcasts' === $this->currentRoute);
        }
        if ($this->isDomestic || $this->availableEpisodeCount > 0) {
            $items[] = new AvailableNow($this->pid, 'programme_episodes_player' === $this->currentRoute, $this->availableEpisodeCount);
        }
        if ($this->hasBroadcasts) {
            $items[] = new NextOn($this->pid, 'programme_upcoming_broadcasts' === $this->currentRoute, $this->upcomingBroadcastCount);
        }

        return $items;
    }
}
