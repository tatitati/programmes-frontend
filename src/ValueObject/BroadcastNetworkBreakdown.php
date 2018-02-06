<?php
declare(strict_types=1);

namespace App\ValueObject;

use BBC\ProgrammesPagesService\Domain\Entity\Network;

class BroadcastNetworkBreakdown
{
    /** @var Network */
    private $network;

    /** @var string */
    private $networkName;

    /** @var string */
    private $servicesNames;

    public function __construct(string $networkName, string $servicesNames, ?Network $network)
    {
        $this->networkName = $networkName;
        $this->servicesNames = $servicesNames;
        $this->network = $network;
    }

    public function getNetwork(): Network
    {
        return $this->network;
    }

    public function getNetworkName(): string
    {
        return $this->networkName;
    }

    public function getServicesNames(): string
    {
        return $this->servicesNames;
    }
}
