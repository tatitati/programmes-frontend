<?php
declare(strict_types = 1);

namespace App\Ds2013\Molecule\DateList;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Chronos;

class DateListItemPresenter extends Presenter
{
    /** @inheritDoc */
    protected $options = [
        'user_timezone' => 'GMT',
    ];

    /** @var Chronos */
    private $datetime;

    /** @var Service */
    private $service;

    /** @var int */
    private $offset;

    public function __construct(Chronos $datetime, Service $service, int $offset, array $options = [])
    {
        parent::__construct($options);

        $this->datetime = $datetime->addDays($offset);
        $this->service = $service;
        $this->offset = $offset;
    }

    public function getDatetime(): Chronos
    {
        return $this->datetime;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getServicePid(): string
    {
        return (string) $this->service->getPid();
    }

    public function isLink(): bool
    {
        // if the date is more than 90 DAYS from now, then don't allow a link (page will still exist)
        return $this->offset != 0 &&
            $this->datetime->lt(new Chronos('90 days')) &&
            $this->service->isActiveAt($this->datetime);
    }

    public function isGmt(): bool
    {
        return $this->options['user_timezone'] == 'GMT';
    }
}
