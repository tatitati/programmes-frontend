<?php

namespace App\Ds2013\Presenters\Section\EpisodesSubNav\NavigationItems;

class ByDate extends NavigationItem
{
    public function getTranslationString(): string
    {
        return 'by_date';
    }

    public function getRoute(): string
    {
        return 'programme_broadcasts';
    }
}
