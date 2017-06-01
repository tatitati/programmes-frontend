<?php
declare(strict_types = 1);
namespace App\Ds2013\Page\Schedules\RegionPick;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Service;

class RegionPickPresenter extends Presenter
{
    /**@var Service */
    private $service;

    /** @var string|null */
    private $date;

    /** @var Service[] */
    private $servicesInNetwork;

    public function __construct(Service $service, ?string $date, array $servicesInNetwork, array $options = [])
    {
        parent::__construct($options);
        $this->service = $service;
        $this->date = $date;
        $this->servicesInNetwork = $servicesInNetwork;
    }

    public function hasRegionPicker(): bool
    {
        return count($this->servicesInNetwork) > 1;
    }

    public function getServiceName(): string
    {
        return $this->service->getName();
    }

    public function getLinkMessage(): string
    {
        $twin = $this->getTwinService();
        if ($twin) {
            return 'schedules_regional_changeto';
        }
        return $this->service->isTv() ? 'schedules_regional' : 'schedules_regional_change';
    }

    public function getLinkName(): string
    {
        $twin = $this->getTwinService();
        return $twin ? $twin->getName() : $this->service->getNetwork()->getName();
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function getTwinServicePid(): string
    {
        $twin = $this->getTwinService();
        return $twin ? (string) $twin->getPid() : '';
    }

    private function getTwinService(): ?Service
    {
        if (count($this->servicesInNetwork) != 2) {
            return null;
        }

        // If there are two services, find the "other" service
        $otherServices = array_filter($this->servicesInNetwork, function (Service $sisterService) {
            return ($this->service->getSid() != $sisterService->getSid());
        });
        return reset($otherServices);
    }
}
