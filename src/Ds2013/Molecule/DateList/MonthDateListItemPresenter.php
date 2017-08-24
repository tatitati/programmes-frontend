<?php
declare(strict_types = 1);

namespace App\Ds2013\Molecule\DateList;

use App\ValueObject\BroadcastMonth;
use App\ValueObject\BroadcastPeriod;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Chronos;
use Cake\Chronos\ChronosInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MonthDateListItemPresenter extends AbstractDateListItemPresenter
{
    public function __construct(UrlGeneratorInterface $router, ChronosInterface $datetime, Service $service, int $offset, Chronos $unavailableAfterDate, array $options = [])
    {
        parent::__construct($router, $datetime->addMonths($offset), $service, $offset, $unavailableAfterDate, $options);
    }

    public function getLink(): string
    {
        return $this->router->generate(
            'schedules_by_month',
            ['pid' => (string) $this->service->getPid(), 'date' => $this->datetime->format('Y-m')],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    protected function getBroadcastPeriod(string $medium): BroadcastPeriod
    {
        return new BroadcastMonth($this->datetime, $medium);
    }
}
