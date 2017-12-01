<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Domain\SupportingContent;

use App\DsAmen\Presenter;
use App\ExternalApi\Electron\Domain\SupportingContentItem;

class SupportingContentPresenter extends Presenter
{
    /** @var SupportingContentItem */
    private $supportingContent;

    protected $options = [
        // display options
        'show_image' => true,
    ];

    public function __construct(SupportingContentItem $supportingContent, array $options = [])
    {
        $this->supportingContent = $supportingContent;
        parent::__construct($options);
    }

    public function getSupportingContent(): SupportingContentItem
    {
        return $this->supportingContent;
    }

    public function showImage(): bool
    {
        return ($this->supportingContent->getImage() && $this->options['show_image']);
    }
}
