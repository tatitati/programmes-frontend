<?php
declare(strict_types = 1);

namespace App\Ds2013\Molecule\DateList;

use App\ValueObject\BroadcastDay;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Chronos;
use Cake\Chronos\ChronosInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DayDateListItemPresenter extends AbstractDateListItemPresenter
{
    public function __construct(UrlGeneratorInterface $router, ChronosInterface $datetime, Service $service, int $offset, array $options = [])
    {
        $options = array_merge(['user_timezone' => 'GMT'], $options);
        parent::__construct($router, $datetime->addDays($offset), $service, $offset, $options);
    }

    public function getLink(): string
    {
        return $this->router->generate(
            'schedules_by_day',
            ['pid' => (string) $this->service->getPid(), 'date' => $this->datetime->format('Y-m-d')],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function isGmt(): bool
    {
        return $this->options['user_timezone'] == 'GMT';
    }

    public function isLink(): bool
    {
        $network = $this->service->getNetwork();
        if (is_null($network)) {
            return false;
        }
        $broadcastDay = new BroadcastDay($this->datetime, $network->getMedium());
        // if the date is more than 90 DAYS from now, then don't allow a link (page will still exist)
        return $this->offset != 0 &&
            $this->datetime->lt(new Chronos('90 days')) &&
            $broadcastDay->serviceIsActiveOnThisDay($this->service);
    }
}
