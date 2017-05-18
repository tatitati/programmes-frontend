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

    public function getStartAt(): DateTimeImmutable
    {
        return $this->broadcast->getStartAt();
    }

    public function getEndAt(): DateTimeImmutable
    {
        return $this->broadcast->getEndAt();
    }

    public function getServicePid(): string
    {
        return (string) $this->broadcast->getService()->getPid();
    }

    public function isOnAirNow(): bool
    {
        return $this->broadcast->isOnAirAt($this->now);
    }

    public function getNetworkMedium(): string
    {
        return $this->broadcast->getProgrammeItem()->getNetwork()->getMedium();
    }



    public function getProgrammeItem()
    {
        return $this->broadcast->getProgrammeItem();
    }

    public function getInfoClasses(): string
    {
        return $this->buildCssClasses([
            '1/4 1/6@bpb2 1/6@bpw' => $this->getOption('is_stacked'),
        ]);
    }

    public function getMessageIsNow(): string
    {
        if ($this->getNetworkMedium() == 'tv') {
            return 'on_now';
        }

        return 'on_air';
    }

    public function getObjectClasses(): string
    {
        return $this->buildCssClasses([
            'broadcast' => true,
            'broadcast--has-ended' => $this->isInThePast(),
            'block-link block-link--steal' => $this->getOption('steal_blocklink'),
            $this->getOption('container_classes') => !empty($this->getOption('container_classes')),
            $this->getOption('highlight_box_classes') => !empty($this->getOption('highlight_box_classes')),
            'br-keyline br-blocklink-page br-page-linkhover-onbg015--hover' => $this->getOption('highlight_box_classes'),
            'br-box-subtle highlight-box--active' => $this->getOption('steal_blocklink') && $this->isOnAirNow(),
        ]);
    }

    public function getProgrammeClasses()
    {
        return $this->buildCssClasses([
                '3/4 5/6@bpb2 5/6@bpw' => !$this->getOption('is_stacked'),
        ]);
    }

    private function isInThePast(): bool
    {
        return $this->broadcast->getEndAt() < $this->now;
    }
}
