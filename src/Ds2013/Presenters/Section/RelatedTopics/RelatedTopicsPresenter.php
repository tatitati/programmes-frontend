<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Section\RelatedTopics;

use App\Ds2013\Presenter;

class RelatedTopicsPresenter extends Presenter
{
    /** @var array AdaClass[] */
    private $relatedTopics;

    public function __construct(array $relatedTopics, array $options = [])
    {
        parent::__construct($options);

        $this->relatedTopics = $relatedTopics;
    }

    public function getRelatedTopics(): array
    {
        return $this->relatedTopics;
    }
}
