<?php
declare(strict_types = 1);
namespace App\Ds2013\Page\Schedules\ByDayPage;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\BroadcastGap;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Chronos;

class SchedulesByDayPagePresenter extends Presenter
{
    /** @var Service */
    private $service;

    /** @var Chronos */
    private $broadcastDayStart;

    /** @var Broadcast[] */
    private $broadcasts;

    /** @var Service[] */
    private $servicesInNetwork;

    /** @var string|null */
    private $routeDate;

    /** @var Service */
    private $twinService;

    /** @var Broadcast */
    private $onAirBroadcast = false;

    /** @var CollapsedBroadcast|null */
    private $liveCollapsedBroadcast;

    /** @var Network[] */
    private $otherNetworks;

    public function __construct(
        Service $service,
        Chronos $broadcastDayStart,
        array $broadcasts,
        ?string $routeDate,
        array $servicesInNetwork,
        array $otherNetworks,
        ?CollapsedBroadcast $liveCollapsedBroadcast,
        array $options = []
    ) {
        parent::__construct($options);
        $this->service = $service;
        $this->broadcastDayStart = $broadcastDayStart;
        $this->broadcasts = $broadcasts;
        $this->routeDate = $routeDate;
        $this->servicesInNetwork = $servicesInNetwork;
        $this->liveCollapsedBroadcast = $liveCollapsedBroadcast;
        $this->otherNetworks = $otherNetworks;

        $this->twinService = $this->twinService();
    }

    public function getOtherNetworks()
    {
        return $this->otherNetworks;
    }

    public function getService(): Service
    {
        return $this->service;
    }

    public function getBroadcastDayStart(): Chronos
    {
        return $this->broadcastDayStart;
    }

    public function getRouteDate(): ?string
    {
        return $this->routeDate;
    }

    public function getServicesInNetwork(): array
    {
        return $this->servicesInNetwork;
    }

    public function getLiveCollapsedBroadcast(): ?CollapsedBroadcast
    {
        return $this->liveCollapsedBroadcast;
    }

    public function getServicesHeadingMessage()
    {
        return $this->service->isTv() ? 'schedules_regional_note' : 'schedules_regional_note_radio';
    }

    public function getSiblingServicesLinkMessage(): string
    {
        if ($this->twinService) {
            return 'schedules_regional_changeto';
        }
        return $this->service->isTv() ? 'schedules_regional' : 'schedules_regional_change';
    }

    public function getSiblingServicesLinkName(): string
    {
        return $this->twinService ? $this->twinService->getShortName() : $this->service->getNetwork()->getName();
    }

    public function getTwinServicePid(): string
    {
        return $this->twinService ? (string) $this->twinService->getPid() : '';
    }

    public function getBroadcastsGroupedByPeriodOfDay(): array
    {
        $intervalsDay = [
            'early' => [],
            'morning' => [],
            'afternoon' => [],
            'evening' => [],
            'late' => [],
        ];

        $priorBroadcast = null;
        foreach ($this->broadcasts as $broadcast) {
            // If there is space between the start of the current broadcast and
            // the end of the prior broadcast then inject a broadcast gap
            if ($priorBroadcast && $broadcast->getStartAt()->gt($priorBroadcast->getEndAt())) {
                $broadcastGap = new BroadcastGap(
                    $this->service,
                    $priorBroadcast->getEndAt(),
                    $broadcast->getStartAt()
                );

                $period = $this->getBroadcastPeriodWord($broadcastGap, $this->broadcastDayStart);
                $intervalsDay[$period][] = $broadcastGap;
            }

            $period = $this->getBroadcastPeriodWord($broadcast, $this->broadcastDayStart);
            $intervalsDay[$period][] = $broadcast;

            $priorBroadcast = $broadcast;
        }

        return array_filter($intervalsDay);
    }

    public function getOnAirBroadcast(): ?Broadcast
    {
        if ($this->onAirBroadcast !== false) {
            return $this->onAirBroadcast;
        }

        $this->onAirBroadcast = null;
        foreach ($this->broadcasts as $broadcast) {
            if ($broadcast->isOnAir()) {
                $this->onAirBroadcast = $broadcast;
                break;
            }
        }
        return $this->onAirBroadcast;
    }

    public function hasBroadcasts(): bool
    {
        return !!$this->broadcasts;
    }

    public function hasJumpLinks(): bool
    {
        return !!$this->broadcasts && !$this->service->isTv();
    }

    public function hasSiblingServiceLink(): bool
    {
        return count($this->servicesInNetwork) > 1;
    }

    public function hasSiblingServiceList(): bool
    {
        return count($this->servicesInNetwork) > 2;
    }

    /**
     * Early - midnight until 6am
     * Morning - 6am until midday
     * Afternoon - midday until 6pm
     * Evening - 6pm until midnight
     * Late - midnight until 6am the next day
     *
     * @param Broadcast|BroadcastGap $broadcast
     * @param Chronos $selectedDate
     * @return string
     */
    private function getBroadcastPeriodWord($broadcast, Chronos $selectedDate): string
    {
        $selectedDayEnd = $selectedDate->endOfDay();

        $startBroadcast = $broadcast->getStartAt()->setTimezone(ApplicationTime::getLocalTimeZone());
        $startBroadcastHour = $startBroadcast->format('H');

        // Need to check for 'late' first as these broadcasts are actually the day after the selected date
        if ($startBroadcast > $selectedDayEnd) {
            return 'late';
        }

        if ($startBroadcastHour < 6) {
            return 'early';
        }

        if ($startBroadcastHour < 12) {
            return 'morning';
        }

        if ($startBroadcastHour < 18) {
            return 'afternoon';
        }

        return 'evening';
    }

    private function twinService(): ?Service
    {
        if (count($this->servicesInNetwork) != 2) {
            return null;
        }

        // If there are two services, find the "other" service
        $otherServices = array_filter($this->servicesInNetwork, function (Service $sisterService) {
            return ($this->service->getSid() != $sisterService->getSid());
        });
        return reset($otherServices);
    }
}
