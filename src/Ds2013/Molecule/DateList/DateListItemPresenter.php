<?php
declare(strict_types = 1);

namespace App\Ds2013\Molecule\DateList;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use DateInterval;
use DateTimeImmutable;

class DateListItemPresenter extends Presenter
{
    /** @inheritDoc */
    protected $options = [
        'user_timezone' => 'GMT',
    ];

    /** @var DateTimeImmutable */
    private $datetime;

    /** @var Service */
    private $service;

    /** @var int */
    private $offset;

    public function __construct(DateTimeImmutable $datetime, Service $service, int $offset, array $options = [])
    {
        parent::__construct($options);
        if ($offset < 0) {
            $this->datetime = $datetime->sub(new DateInterval('P' . abs($offset) . 'D'));
        } else {
            $this->datetime = $datetime->add(new DateInterval('P' . $offset . 'D'));
        }
        $this->service = $service;
        $this->offset = $offset;
    }

    public function getAttributesForItem(): string
    {
        if ($this->options['user_timezone'] == 'GMT') {
            return '';
        }
        return $this->buildHtmlAttributes(['data-href-add-utcoffset' => 'true']);
    }

    public function getDatetime(): DateTimeImmutable
    {
        return $this->datetime;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function isToday(): bool
    {
        return ApplicationTime::getTime()->format('Y-m-d') == $this->datetime->format('Y-m-d');
    }

    public function getServicePid(): string
    {
        return (string) $this->service->getPid();
    }

    public function isLink(): bool
    {
        // if the date is more than 90 DAYS from now, then don't allow a link (page will still exist)
        return $this->offset != 0 &&
            ApplicationTime::getTime()->add(new DateInterval('P90D')) > $this->datetime &&
            $this->service->isActiveAt($this->datetime);
    }
}
