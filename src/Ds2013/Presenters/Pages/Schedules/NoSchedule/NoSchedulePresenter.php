<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Pages\Schedules\NoSchedule;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Chronos;
use Cake\Chronos\ChronosInterface;

class NoSchedulePresenter extends Presenter
{
    /** @var ChronosInterface */
    private $end;

    /** @var Service */
    private $service;

    /** @var ChronosInterface */
    private $start;

    public function __construct(Service $service, ChronosInterface $start, ChronosInterface $end, array $options = [])
    {
        parent::__construct($options);
        $this->service = $service;
        $this->start = $start;
        $this->end = $end;
    }

    public function finished(): bool
    {
        return $this->service->getEndDate() && $this->start->gt($this->service->getEndDate());
    }

    public function notBegunYet(): bool
    {
        return $this->service->getStartDate() && $this->end->lt($this->service->getStartDate());
    }

    public function serviceEndDate(): Chronos
    {
        return new Chronos($this->service->getEndDate());
    }

    public function servicePid(): string
    {
        return (string) $this->service->getPid();
    }

    public function serviceStartDate(): Chronos
    {
        return new Chronos($this->service->getStartDate());
    }
}
