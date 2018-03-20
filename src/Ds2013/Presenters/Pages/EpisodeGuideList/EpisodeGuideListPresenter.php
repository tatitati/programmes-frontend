<?php
declare(strict_types=1);

namespace App\Ds2013\Presenters\Pages\EpisodeGuideList;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;

class EpisodeGuideListPresenter extends Presenter
{
    /** @var ProgrammeContainer */
    private $contextProgramme;

    /** @var Programme[] */
    private $programmes;

    /** @var CollapsedBroadcast[] */
    private $upcomingBroadcasts;

    /** @var int */
    private $nestedLevel;

    public function __construct(ProgrammeContainer $contextProgramme, array $programmes, array $upcomingBroadcasts, int $nestedLevel)
    {
        // This page has no options
        parent::__construct([]);
        $this->programmes = $programmes;
        $this->contextProgramme = $contextProgramme;
        $this->upcomingBroadcasts = $upcomingBroadcasts;
        $this->nestedLevel = $nestedLevel;
    }

    /** @return Programme[] */
    public function getProgrammes(): array
    {
        return $this->programmes;
    }

    public function getContextProgramme(): ProgrammeContainer
    {
        return $this->contextProgramme;
    }

    public function getUpcomingBroadcastsFromProgramme(Pid $pid) :?CollapsedBroadcast
    {
        if (isset($this->upcomingBroadcasts[(string) $pid])) {
            return $this->upcomingBroadcasts[(string) $pid];
        }

        return null;
    }

    public function getQueryParamForNextNestedLevel() :string
    {
        return "nestedlevel=" . ($this->nestedLevel + 1);
    }

    public function getHeadingTag(): string
    {
        return 'h' . $this->nestedLevel;
    }

    public function getNextOnHeadingTag(): string
    {
        return 'h' . ($this->nestedLevel + 1);
    }
}
