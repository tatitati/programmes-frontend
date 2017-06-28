<?php
declare(strict_types = 1);
namespace App\Ds2013\Organism\Broadcast;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\BroadcastGap;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use Cake\Chronos\Chronos;
use InvalidArgumentException;

class BroadcastPresenter extends Presenter
{
    protected $options = [
        'highlight_box_classes' => '',
        'container_classes' => '',
        'is_stacked' => false,
        'show_date' => false,
        'steal_blocklink' => true,
    ];

    /** @var Broadcast|BroadcastGap */
    private $broadcast;

    /** @var CollapsedBroadcast */
    private $collapsedBroadcast;

    /** @var Chronos */
    private $now;

    /**
     * BroadcastPresenter constructor.
     * @param Broadcast|BroadcastGap $broadcast
     * @param CollapsedBroadcast|null $collapsedBroadcast
     * @param array $options
     */
    public function __construct(
        $broadcast,
        ?CollapsedBroadcast $collapsedBroadcast = null,
        array $options = []
    ) {
        if (!($broadcast instanceof Broadcast || $broadcast instanceof BroadcastGap)) {
            throw new InvalidArgumentException(sprintf(
                'Expected $broadcast to be an instance of "%s" or "%s". Found instance of "%s"',
                Broadcast::CLASS,
                BroadcastGap::CLASS,
                (is_object($broadcast) ? get_class($broadcast) : gettype($broadcast))
            ));
        }
        parent::__construct($options);
        $this->broadcast = $broadcast;
        $this->collapsedBroadcast = $collapsedBroadcast;
        $this->now = Chronos::now('Europe/London');
    }

    /**
     * @return Broadcast|BroadcastGap
     */
    public function getBroadcast()
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
        if ($this->broadcast instanceof BroadcastGap) {
            return null;
        }

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

    public function getCollapsedBroadcast(): ?CollapsedBroadcast
    {
        return $this->collapsedBroadcast;
    }
}
