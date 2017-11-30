<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Utilities\Calendar;

use App\Ds2013\Presenter;
use App\Exception\InvalidOptionException;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Date;

class CalendarPresenter extends Presenter
{
    /** @inheritDoc */
    protected $options = [
        'hide_caption' => false,
    ];

    /** @var Date */
    private $date;

    /**
     * Don't show links before this date
     * @var null|Date
     */
    private $lowerLinkCutOff;

    /**
     * Don't show links after this date
     * @var Date
     */
    private $upperLinkCutOff;

    /** @var Service */
    private $service;

    public function __construct(Date $date, Service $service, array $options = [])
    {
        parent::__construct($options);
        $this->date = $date;
        $this->service = $service;
        $this->lowerLinkCutOff =  $service->getStartDate() ? new Date($service->getStartDate()) : null;
        $cutOffDate = new Date('+35 days');
        $this->upperLinkCutOff =  new Date($service->getEndDate() && $service->getEndDate()->lt($cutOffDate) ? $service->getEndDate() : $cutOffDate);
    }

    public function getFirstOfMonth(): Date
    {
        return $this->date;
    }

    public function getLowerLinkCutOff(): ?Date
    {
        return $this->lowerLinkCutOff;
    }

    public function getUpperLinkCutOff(): Date
    {
        return $this->upperLinkCutOff;
    }

    public function getPid(): string
    {
        return (string) $this->service->getPid();
    }

    protected function validateOptions(array $options): void
    {
        parent::validateOptions($options);

        if (!is_bool($this->getOption('hide_caption'))) {
            throw new InvalidOptionException("hide_caption must a bool");
        }
    }
}
