<?php
declare(strict_types = 1);
namespace App\Ds2013\Presenters\Domain\Broadcast;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\SingleServiceBroadcastInfoInterface;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use Cake\Chronos\Chronos;

class BroadcastPresenter extends Presenter
{
    protected $options = [
        'highlight_box_classes' => '',
        'container_classes' => '',
        'is_stacked' => false,
        'show_date' => false,
        'show_image' => true,
        'show_overlay' => true,
        'show_resume_at' => true,
        'steal_blocklink' => true,
    ];

    /** @var SingleServiceBroadcastInfoInterface */
    private $broadcast;

    /** @var CollapsedBroadcast */
    private $collapsedBroadcast;

    public function __construct(
        SingleServiceBroadcastInfoInterface $broadcast,
        ?CollapsedBroadcast $collapsedBroadcast = null,
        array $options = []
    ) {
        parent::__construct($options);
        $this->broadcast = $broadcast;
        $this->collapsedBroadcast = $collapsedBroadcast;
    }

    public function getBroadcast(): SingleServiceBroadcastInfoInterface
    {
        return $this->broadcast;
    }

    public function getServiceName(): string
    {
        return $this->broadcast->getService()->getName();
    }

    public function getServicePid(): string
    {
        return (string) $this->broadcast->getService()->getPid();
    }

    public function getStartAt(): Chronos
    {
        return new Chronos($this->broadcast->getStartAt());
    }

    public function getEndAt(): Chronos
    {
        return new Chronos($this->broadcast->getEndAt());
    }

    public function getProgrammeItem()
    {
        if ($this->broadcast instanceof Broadcast) {
            return $this->broadcast->getProgrammeItem();
        }

        return null;
    }

    public function getOnAirMessage(): string
    {
        return $this->broadcast->getService()->isTv() ? 'on_now' : 'on_air';
    }

    public function isOnAirNow(): bool
    {
        return $this->broadcast->isOnAirAt(ApplicationTime::getTime());
    }

    public function isInThePast(): bool
    {
        return $this->broadcast->getEndAt() < ApplicationTime::getTime();
    }

    public function getCollapsedBroadcast(): ?CollapsedBroadcast
    {
        return $this->collapsedBroadcast;
    }
}
