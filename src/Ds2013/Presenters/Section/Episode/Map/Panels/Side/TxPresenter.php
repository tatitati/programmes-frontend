<?php
declare(strict_types=1);
namespace App\Ds2013\Presenters\Section\Episode\Map\Panels\Side;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;

class TxPresenter extends Presenter
{
    /** @var Episode */
    protected $episode;

    /** @var CollapsedBroadcast|null */
    protected $upcomingBroadcast;

    /** @var CollapsedBroadcast|null */
    protected $lastOnBroadcast;

    public function __construct(Episode $episode, ?CollapsedBroadcast $upcomingBroadcast, ?CollapsedBroadcast $lastOnBroadcast)
    {
        parent::__construct();
        $this->episode = $episode;
        $this->upcomingBroadcast = $upcomingBroadcast;
        $this->lastOnBroadcast = $lastOnBroadcast;
    }
}
