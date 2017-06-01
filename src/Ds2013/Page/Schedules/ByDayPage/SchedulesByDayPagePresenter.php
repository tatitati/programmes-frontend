<?php
declare(strict_types = 1);
namespace App\Ds2013\Page\Schedules\ByDayPage;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use DateTimeImmutable;
use DateTimeZone;

class SchedulesByDayPagePresenter extends Presenter
{
    /** @var Service */
    private $service;

    /** @var DateTimeImmutable */
    private $startDate;

    /** @var DateTimeImmutable */
    private $endDate;

    /** @var Broadcast[] */
    private $broadcasts;

    /** @var Service[] */
    private $servicesInNetwork;

    /** @var Broadcast */
    private $onAirBroadcast = false;

    public function __construct(
        Service $service,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        array $broadcasts,
        array $servicesInNetwork,
        array $options = []
    ) {
        parent::__construct($options);
        $this->service = $service;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->broadcasts = $broadcasts;
        $this->servicesInNetwork = $servicesInNetwork;
    }

    public function getService(): Service
    {
        return $this->service;
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getServicesInNetwork(): array
    {
        return $this->servicesInNetwork;
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

        //$prior_broadcast = null;
        foreach ($this->broadcasts as $broadcast) {
            // // If the end of the prior is earlier than the start of this broadcast
            // // then inject a broadcast gap object.
            // if ($prior_broadcast && $prior_broadcast->end->compare($broadcast->start) == -1) {
            //     $period = $this->_getBroadcastPeriod($prior_broadcast->end, $day, $use_timezones);
            //     $periods_of_day[$period][] = $this->_broadcastGap($prior_broadcast->end, $broadcast->start);
            // }

            $period = $this->getBroadcastPeriodWord($broadcast, $this->startDate);
            $intervalsDay[$period][] = $broadcast;
        }

        return array_filter($intervalsDay);
    }

    public function getOnAirBroadcast(): ?Broadcast
    {
        if ($this->onAirBroadcast !== false) {
            return $this->onAirBroadcast;
        }
        $now = ApplicationTime::getTime();

        $this->onAirBroadcast = null;
        foreach ($this->broadcasts as $broadcast) {
            if ($broadcast->isOnAirAt($now)) {
                $this->onAirBroadcast = $broadcast;
                break;
            }
        }
        return $this->onAirBroadcast;
    }
    /**
     * Early - midnight until 6am
     * Morning - 6am until midday
     * Afternoon - midday until 6pm
     * Evening - 6pm until midnight
     * Late - midnight until 6am the next day
     *
     * @param Broadcast $broadcast
     * @param DateTimeImmutable $selectedDate
     * @return string
     */
    private function getBroadcastPeriodWord(Broadcast $broadcast, DateTimeImmutable $selectedDate): string
    {
        $selectedDayEnd = $selectedDate->setTime(23, 59, 59);

        $startBroadcast = $broadcast->getStartAt()->setTimezone(new DateTimeZone('Europe/London'));
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
}
