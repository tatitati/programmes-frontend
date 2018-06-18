<?php
declare(strict_types=1);
namespace App\Ds2013\Presenters\Section\Episode\Map\Panels\Side;

use App\Ds2013\Presenter;

class EmptyPresenter extends Presenter
{
    public function getDataColumnAttribute(): string
    {
        return 'empty';
    }
}
