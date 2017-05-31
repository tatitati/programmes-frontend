<?php
declare(strict_types = 1);
namespace App\Ds2013\Organism\Broadcast;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use DateTimeImmutable;

class BroadcastPresenter extends Presenter
{
    protected $options = [
        'highlight_box_classes' => '',
        'container_classes' => '',
        'is_stacked' => false,
        'show_date' => false,
        'steal_blocklink' => true,
    ];

    /** @var Broadcast */
    private $broadcast;

    private $now;

    public function __construct(
        Broadcast $broadcast,
        array $options = []
    ) {
        parent::__construct($options);
        $this->broadcast = $broadcast;
        $this->now = ApplicationTime::getTime();
    }

    public function getServiceName(): string
    {
        return $this->broadcast->getService()->getName();
    }

    public function getServicePid(): string
    {
        return (string) $this->broadcast->getService()->getPid();
    }

    public function getStartAt(): DateTimeImmutable
    {
        return $this->broadcast->getStartAt();
    }

    public function getEndAt(): DateTimeImmutable
    {
        return $this->broadcast->getEndAt();
    }

    public function getProgrammeItem()
    {
        return $this->broadcast->getProgrammeItem();
    }

    public function getOnAirMessage(): string
    {
        return $this->broadcast->getService()->isTv() ? 'on_now' : 'on_air';
    }

    public function isOnAirNow(): bool
    {
        return $this->broadcast->isOnAirAt($this->now);
    }

    public function isInThePast(): bool
    {
        return $this->broadcast->getEndAt() < $this->now;
    }
}
