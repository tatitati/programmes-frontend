<?php
declare(strict_types=1);
namespace App\Ds2013\Presenters\Section\Episode\Map\Panels\Main;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;

class PanelPlayoutPresenter extends Presenter
{
    /** @var Episode */
    protected $episode;

    public function __construct(Episode $episode)
    {
        parent::__construct();
        $this->episode = $episode;
    }
}
