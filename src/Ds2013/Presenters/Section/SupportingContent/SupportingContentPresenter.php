<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Section\SupportingContent;

use App\Ds2013\Presenter;
use App\ExternalApi\Electron\Domain\SupportingContentItem;

class SupportingContentPresenter extends Presenter
{
    /** @var SupportingContentItem */
    private $supportingContentItem;

    public function __construct(SupportingContentItem $supportingContentItem, array $options = [])
    {
        parent::__construct($options);

        $this->supportingContentItem = $supportingContentItem;
    }

    public function getItem(): SupportingContentItem
    {
        return $this->supportingContentItem;
    }
}
