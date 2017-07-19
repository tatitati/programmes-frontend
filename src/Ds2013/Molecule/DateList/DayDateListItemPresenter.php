<?php
declare(strict_types = 1);

namespace App\Ds2013\Molecule\DateList;

use BBC\ProgrammesPagesService\Domain\Entity\Service;
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
}
