<?php
declare(strict_types=1);
namespace App\Ds2013\Presenters\Section\Episode\Map\Panels\Side;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use InvalidArgumentException;

class TxPresenter extends Presenter
{
    /** @var CollapsedBroadcast */
    private $collapsedBroadcast;

    public function __construct(?CollapsedBroadcast $upcomming, ?CollapsedBroadcast $lastOn)
    {
        if (!$upcomming && !$lastOn) {
            throw new InvalidArgumentException('Tx panel needs a collapsed broadcast');
        }

        parent::__construct();
        $this->collapsedBroadcast = $upcomming ?: $lastOn;
    }

    public function getCollapsedBroadcast() :CollapsedBroadcast
    {
        return $this->collapsedBroadcast;
    }

    public function getDataColumnAttribute(): string
    {
        return 'tx';
    }

    public function getTitle() :?string
    {
        if ($this->collapsedBroadcast->isOnAir()) {
            return $this->collapsedBroadcast->getProgrammeItem()->isRadio() ? 'on_air' : 'on_now';
        }

        if ($this->collapsedBroadcast->getEndAt()->isPast()) {
            return 'last_on';
        }

        return $this->collapsedBroadcast->getProgrammeItem()->isRadio() ? 'on_radio' : 'on_tv';
    }
}
