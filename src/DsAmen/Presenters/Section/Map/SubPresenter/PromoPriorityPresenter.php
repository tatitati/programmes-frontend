<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Section\Map\SubPresenter;

use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\Promotion;

class PromoPriorityPresenter extends LeftColumnPresenter
{
    /** @var Promotion */
    private $promotion;

    public function __construct(ProgrammeContainer $programme, Promotion $promotion, array $options = [])
    {
        parent::__construct($programme, $options);

        $this->promotion = $promotion;
    }

    public function getPromotion(): Promotion
    {
        return $this->promotion;
    }
}
