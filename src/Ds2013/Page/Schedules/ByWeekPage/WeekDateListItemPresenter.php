<?php
declare(strict_types = 1);

namespace App\Ds2013\Page\Schedules\ByWeekPage;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Chronos;

class WeekDateListItemPresenter extends Presenter
{
    /** @var Chronos */
    private $dateTime;

    /**
     * This is sent through to avoid creating a load of Chronos objects when calling isToday()
     * @var Chronos
     */
    private $now;

    /** @var int */
    private $offset;

    /** @var Service */
    private $service;

    public function __construct(int $offset, Service $service, Chronos $startOfWeek, Chronos $now, array $options = [])
    {
        parent::__construct($options);
        $this->offset = $offset;
        $this->service = $service;
        $this->dateTime = $startOfWeek->addDays($this->offset);
        $this->now = $now;
    }

    public function getDatetime(): Chronos
    {
        return $this->dateTime;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getService(): Service
    {
        return $this->service;
    }

    public function isToday(): bool
    {
        return $this->dateTime->toDateString() === $this->now->toDateString();
    }
}
