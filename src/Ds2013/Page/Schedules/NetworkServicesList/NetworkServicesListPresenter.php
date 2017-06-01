<?php
declare(strict_types = 1);
namespace App\Ds2013\Page\Schedules\NetworkServicesList;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Service;

class NetworkServicesListPresenter extends Presenter
{
    /** @var Service */
    private $service;

    /** @var string|null */
    private $date;

    /** @var Service[] */
    private $servicesInNetwork;

    public function __construct(
        Service $service,
        ?string $date,
        array $servicesInNetwork,
        array $options = []
    ) {
        parent::__construct($options);
        $this->service = $service;
        $this->date = $date;
        $this->servicesInNetwork = $servicesInNetwork;
    }

    public function getHeadingMessage()
    {
        return $this->service->isTv() ? 'schedules_regional_note' : 'schedules_regional_note_radio';
    }

    public function getService(): Service
    {
        return $this->service;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function getServicesInNetwork(): array
    {
        return $this->servicesInNetwork;
    }
}
