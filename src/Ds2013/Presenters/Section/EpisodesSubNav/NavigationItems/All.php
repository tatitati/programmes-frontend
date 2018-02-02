<?php

namespace App\Ds2013\Presenters\Section\EpisodesSubNav\NavigationItems;

class All extends NavigationItem
{
    public function getTranslationString(): string
    {
        return 'all';
    }

    public function getRoute(): string
    {
        return 'programme_episodes_guide';
    }
}
