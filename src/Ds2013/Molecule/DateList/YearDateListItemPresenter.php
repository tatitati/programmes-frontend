<?php
declare(strict_types = 1);

namespace App\Ds2013\Molecule\DateList;

use App\ValueObject\BroadcastYear;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Chronos;
use Cake\Chronos\ChronosInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class YearDateListItemPresenter extends AbstractDateListItemPresenter
{
    public function __construct(UrlGeneratorInterface $router, ChronosInterface $datetime, Service $service, int $offset, array $options = [])
    {
        parent::__construct($router, $datetime->addYears($offset), $service, $offset, $options);
    }

    public function getLink(): string
    {
        return $this->router->generate(
            'schedules_by_year',
            ['pid' => (string) $this->service->getPid(), 'year' => $this->datetime->format('Y')],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function isLink(): bool
    {
        // if the date is more than 90 DAYS from now, then don't allow a link (page will still exist)
        if ($this->offset === 0 || $this->datetime->gte(new Chronos('90 days'))) {
            return false;
        }

        $network = $this->service->getNetwork();
        if (is_null($network)) {
            return false;
        }

        $broadcastYear = new BroadcastYear($this->datetime, $network->getMedium());
        return $broadcastYear->serviceIsActiveInThisPeriod($this->service);
    }
}
