<?php

namespace App\Ds2013\Presenters\Section\EpisodesSubNav\NavigationItems;

use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;

abstract class NavigationItem
{
    /** @var bool */
    protected $selected;

    /** @var Pid */
    private $pid;

    public function __construct(Pid $pid, bool $selected)
    {
        $this->selected = $selected;
        $this->pid = $pid;
    }

    abstract public function getTranslationString(): string;

    abstract public function getRoute(): string;

    public function getLinkClass(): string
    {
        if ($this->selected) {
            return 'island--squashed br-box-page br-page-link-ontext br-page-linkhover-ontext--hover';
        }

        return 'island--squashed';
    }

    public function getRouteParams(): array
    {
        return ['pid' => (string) $this->pid];
    }

    public function shouldShowCount(): bool
    {
        return false;
    }

    public function shouldShowLink(): bool
    {
        return true;
    }
}
