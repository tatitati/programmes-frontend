<?php
declare(strict_types=1);

namespace App\Ds2013\Presenters\Section\Segments\SegmentItem;

class GroupPresenter extends AbstractSegmentItemPresenter
{
    /** @var AbstractSegmentItemPresenter[] */
    private $presenters;

    /** @var string */
    private $title;

    public function __construct(string $title, array $presenters, array $options)
    {
        parent::__construct($options);
        $this->presenters = $presenters;
        $this->title = $title;
    }

    /** @return AbstractSegmentItemPresenter[] */
    public function getPresenters(): array
    {
        return $this->presenters;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
