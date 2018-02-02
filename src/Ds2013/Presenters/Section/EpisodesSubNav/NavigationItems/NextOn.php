<?php

namespace App\Ds2013\Presenters\Section\EpisodesSubNav\NavigationItems;

use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;

class NextOn extends NavigationItemWithCount
{
    /** @var int */
    private $broadcastCount;

    public function __construct(Pid $pid, bool $selected, int $broadcastCount)
    {
        parent::__construct($pid, $selected);

        $this->broadcastCount = $broadcastCount;
    }

    public function getTranslationString(): string
    {
        return 'next_on';
    }

    public function getRoute(): string
    {
        return 'programme_broadcasts';
    }

    public function getCount(): int
    {
        return $this->broadcastCount;
    }

    public function getRouteParams(): array
    {
        $params = parent::getRouteParams();
        $params['slice'] = 'upcoming';

        return $params;
    }
}
