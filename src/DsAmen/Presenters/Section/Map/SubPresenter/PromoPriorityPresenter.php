<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Section\Map\SubPresenter;

use App\DsAmen\Presenters\Section\Map\SubPresenter\Traits\LeftColumnImageSizeTrait;
use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Promotion;

class PromoPriorityPresenter extends Presenter
{
    use LeftColumnImageSizeTrait;

    /** @var mixed[] */
    protected $options = [
        'is_three_column' => false,
    ];

    /** @var Promotion */
    private $promotion;

    public function __construct(Promotion $promotion, array $options = [])
    {
        parent::__construct($options);
        $this->promotion = $promotion;
    }

    public function getPromotion(): Promotion
    {
        return $this->promotion;
    }
}
