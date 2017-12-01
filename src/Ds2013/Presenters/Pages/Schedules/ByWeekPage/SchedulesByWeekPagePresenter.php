<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Pages\Schedules\ByWeekPage;

use App\Ds2013\Presenters\Utilities\SiblingService\SiblingServicePresenter;
use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Chronos;

class SchedulesByWeekPagePresenter extends Presenter
{
    /** Broadcast[][][] */
    private $broadcasts;

    /**
     * This is sent through to avoid creating a load of Chronos objects when calling isToday()
     * @var Chronos
     */
    private $now;

    /** @var string */
    private $routeDate;

    /** @var Service */
    private $service;

    /** @var Service[] */
    private $servicesInNetwork;

    /** @var Chronos */
    private $startOfWeek;

    /**
     * SchedulesByWeekPagePresenter constructor.
     * @param Service $service
     * @param Chronos $startOfWeek
     * @param Broadcast[][][] $broadcasts
     * @param string $routeDate
     * @param Service[] $servicesInNetwork
     * @param mixed[] $options
     */
    public function __construct(Service $service, Chronos $startOfWeek, array $broadcasts, string $routeDate, array $servicesInNetwork, array $options = [])
    {
        parent::__construct($options);
        $this->servicesInNetwork = $servicesInNetwork;
        $this->service = $service;
        $this->broadcasts = $broadcasts;
        $this->routeDate = $routeDate;
        $this->startOfWeek = $startOfWeek;
        $this->now = ApplicationTime::getTime();
    }

    public function getDateListItem(int $offset): WeekDateListItemPresenter
    {
        return new WeekDateListItemPresenter($offset, $this->service, $this->startOfWeek, $this->now);
    }

    public function getRouteDate(): string
    {
        return $this->routeDate;
    }

    public function getService(): Service
    {
        return $this->service;
    }

    public function getSiblingServicePresenter(): SiblingServicePresenter
    {
        return new SiblingServicePresenter($this->service, 'schedules_by_week', $this->routeDate, $this->servicesInNetwork);
    }

    public function getStartOfWeek(): Chronos
    {
        return $this->startOfWeek;
    }

    public function getTimeSlotItem(int $day, int $hour): TimeSlotItemPresenter
    {
        return new TimeSlotItemPresenter($day, $hour, $this->startOfWeek, $this->now, $this->broadcasts);
    }
}
