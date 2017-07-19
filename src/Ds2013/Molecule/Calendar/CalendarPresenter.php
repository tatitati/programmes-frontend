<?php
declare(strict_types = 1);

namespace App\Ds2013\Molecule\Calendar;

use App\Ds2013\InvalidOptionException;
use App\Ds2013\Presenter;
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

    /** @var Service */
    private $service;

    public function __construct(Date $date, Service $service, array $options = [])
    {
        parent::__construct($options);
        $this->date = $date;
        $this->service = $service;
    }

    public function getFirstOfMonth(): Date
    {
        return $this->date;
    }

    public function getPid(): string
    {
        return (string) $this->service->pid;
    }

    protected function validateOptions(array $options): void
    {
        parent::validateOptions($options);

        if (!is_bool($this->getOption('hide_caption'))) {
            throw new InvalidOptionException("hide_caption must a bool");
        }
    }
}
