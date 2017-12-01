<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Section\Map\SubPresenter;

use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\Promotion;

class ComingSoonPresenter extends RightColumnPresenter
{
    /** @var string */
    private $comingSoonText;

    /** @var Promotion|null */
    private $promotion;

    public function __construct(ProgrammeContainer $programmeContainer, ?Promotion $promotion, array $options = [])
    {
        parent::__construct($programmeContainer, $options);
        $this->comingSoonText = $programmeContainer->getOption('comingsoon_textonly');
        $this->promotion = $promotion;
    }

    public function getPromotion(): ?Promotion
    {
        return $this->promotion;
    }

    public function getComingSoonTextOnly(): string
    {
        return $this->comingSoonText;
    }
}
