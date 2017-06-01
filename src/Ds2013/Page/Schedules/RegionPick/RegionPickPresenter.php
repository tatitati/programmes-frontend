<?php
declare(strict_types = 1);
namespace App\Ds2013\Page\Schedules\RegionPick;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use DateTimeImmutable;

class RegionPickPresenter extends Presenter
{
    /**@var Service */
    private $service;

    /** @var DateTimeImmutable */
    private $date;

    /** @var Service[] */
    private $servicesInNetwork;

    public function __construct(Service $service, DateTimeImmutable $date, array $servicesInNetwork, array $options = [])
    {
        parent::__construct($options);
        $this->service = $service;
        $this->date = $date;
        $this->servicesInNetwork = $servicesInNetwork;
    }

    public function getService(): Service
    {
        return $this->service;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getServicesInNetwork(): array
    {
        return $this->servicesInNetwork;
    }

    public function getTwinService(): ?Service
    {
        $twinService = null;
        if (count($this->servicesInNetwork) == 2) {
            // If there are two services, find the "other" service
            $otherServices = array_filter($this->servicesInNetwork, function (Service $sisterService) use ($service) {
                return ($service->getSid() !== $sisterService->getSid());
            });
            $twinService = reset($otherServices);
        }
        return $twinService;
    }
}
