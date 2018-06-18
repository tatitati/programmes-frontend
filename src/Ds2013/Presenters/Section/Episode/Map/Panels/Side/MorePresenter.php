<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Section\Episode\Map\Panels\Side;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;

class MorePresenter extends Presenter
{
    /** @var Episode */
    protected $episode;

    /** @var Episode|null */
    private $nextEpisode;

    /** @var Episode|null */
    private $previousEpisode;

    public function __construct(Episode $episode, ?Episode $nextEpisode, ?Episode $previousEpisode)
    {
        parent::__construct();
        $this->episode = $episode;
        $this->nextEpisode = $nextEpisode;
        $this->previousEpisode = $previousEpisode;
    }

    public function getDataColumnAttribute(): string
    {
        return 'more';
    }

    public function getEpisode(): Episode
    {
        return $this->episode;
    }

    public function getGroupSize(): ?int
    {
        /** @var ProgrammeContainer $parent|null */
        $parent = $this->episode->getParent();

        return $parent ? $parent->getExpectedChildCount() : null;
    }

    public function getNextEpisode(): ?Episode
    {
        return $this->nextEpisode;
    }

    public function getPreviousEpisode(): ?Episode
    {
        return $this->previousEpisode;
    }
}
