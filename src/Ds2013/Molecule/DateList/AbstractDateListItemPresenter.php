<?php
declare(strict_types = 1);

namespace App\Ds2013\Molecule\DateList;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Chronos;
use Cake\Chronos\ChronosInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractDateListItemPresenter extends Presenter
{
    /** @var ChronosInterface */
    protected $datetime;

    /** @var int */
    protected $offset;

    /** @var UrlGeneratorInterface */
    protected $router;

    /** @var Service */
    protected $service;

    public function __construct(UrlGeneratorInterface $router, ChronosInterface $datetime, Service $service, int $offset, array $options = [])
    {
        parent::__construct($options);

        $this->datetime = $datetime;
        $this->service = $service;
        $this->offset = $offset;
        $this->router = $router;
    }

    abstract public function getLink(): string;

    public function getDateTime(): ChronosInterface
    {
        return $this->datetime;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getTemplateVariableName(): string
    {
        return 'date_list_item';
    }

    public function isLink(): bool
    {
        // if the date is more than 90 DAYS from now, then don't allow a link (page will still exist)
        return $this->offset != 0 &&
            $this->datetime->lt(new Chronos('90 days')) &&
            $this->service->isActiveAt($this->datetime);
    }
}
