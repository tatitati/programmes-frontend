<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Map\SubPresenter;

use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;

class LastOnPresenter extends RightColumnPresenter
{
    /** @var CollapsedBroadcast|null  */
    private $lastOn;

    public function __construct(ProgrammeContainer $programmeContainer, ?CollapsedBroadcast $lastOn, array $options = [])
    {
        parent::__construct($programmeContainer, $options);
        $this->lastOn = $lastOn;
    }

    public function getLastOn(): ?CollapsedBroadcast
    {
        return $this->lastOn;
    }

    public function showImage(): bool
    {
        // Only show image if it's not a minimap and the programme item image is different from the context programme image
        return !$this->getOption('show_mini_map') &&
            (string) $this->lastOn->getProgrammeItem()->getImage()->getPid() !==
            (string) $this->programmeContainer->getImage()->getPid();
    }
}
