<?php

namespace App\Ds2013\Presenters\Section\EpisodesSubNav\NavigationItems;

abstract class NavigationItemWithCount extends NavigationItem
{
    abstract public function getCount(): int;

    public function getLinkClass(): string
    {
        if ($this->shouldShowLink()) {
            return parent::getLinkClass();
        }
        if ($this->selected) {
            return 'island--squashed br-box-page br-page-text-ontext';
        }

        return 'island--squashed text--subtle';
    }

    public function shouldShowCount(): bool
    {
        return true;
    }

    public function shouldShowLink(): bool
    {
        return $this->getCount() > 0;
    }
}
