<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Domain\ContentBlock\Promotions;

use App\Ds2013\Presenters\Domain\ContentBlock\ContentBlockPresenter;
use App\ExternalApi\Isite\Domain\ContentBlock\Promotions;

class PromotionsPresenter extends ContentBlockPresenter
{
    public function __construct(Promotions $promotionsBlock, bool $inPrimaryColumn, array $options = [])
    {
        parent::__construct($promotionsBlock, $inPrimaryColumn, $options);
    }

    public function getPositionType(): string
    {
        if ($this->inPrimaryColumn) {
            return 'page';
        }

        return 'subtle';
    }
}
