<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Utilities\SiblingService;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Service;

class SiblingServicePresenter extends Presenter
{
    /** @var Service */
    private $service;

    /** @var Service[] */
    private $servicesInNetwork;

    /** @var string|null */
    private $routeDate;

    /** @var string */
    private $routeName;

    public function __construct(Service $service, string $routeName, ?string $routeDate, array $servicesInNetwork, array $options = [])
    {
        parent::__construct($options);
        $this->servicesInNetwork = $servicesInNetwork;
        $this->service = $service;
        $this->routeDate = $routeDate;
        $this->routeName = $routeName;
    }

    public function getRouteDate(): ?string
    {
        return $this->routeDate;
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    public function getService(): Service
    {
        return $this->service;
    }

    public function getServicesHeadingMessage(): string
    {
        return $this->service->isTv() ? 'schedules_regional_note' : 'schedules_regional_note_radio';
    }

    /**
     * @return Service[]
     */
    public function getServicesInNetwork(): array
    {
        return $this->servicesInNetwork;
    }

    public function hasSiblingServiceList(): bool
    {
        return count($this->servicesInNetwork) > 2;
    }
}
