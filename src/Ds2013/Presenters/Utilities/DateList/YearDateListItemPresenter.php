<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Utilities\DateList;

use App\ValueObject\BroadcastPeriod;
use App\ValueObject\BroadcastYear;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Chronos;
use Cake\Chronos\ChronosInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class YearDateListItemPresenter extends AbstractDateListItemPresenter
{
    public function __construct(UrlGeneratorInterface $router, ChronosInterface $datetime, Service $service, int $offset, Chronos $unavailableAfterDate, array $options = [])
    {
        parent::__construct($router, $datetime->addYears($offset), $service, $offset, $unavailableAfterDate, $options);
    }

    public function getLink(): string
    {
        return $this->router->generate(
            'schedules_by_year',
            ['pid' => (string) $this->service->getPid(), 'year' => $this->datetime->format('Y')],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }



    protected function getBroadcastPeriod(string $medium): BroadcastPeriod
    {
        return new BroadcastYear($this->datetime, $medium);
    }
}
