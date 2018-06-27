<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Section\RelatedTopics;

use App\Ds2013\Presenter;
use App\ExternalApi\Ada\Domain\AdaClass;

class RelatedTopicsPresenter extends Presenter
{
    /** @var string */
    private $linkTrack;

    /** @var array AdaClass[] */
    private $relatedTopics;

    public function __construct(array $relatedTopics, string $linkTrack, array $options = [])
    {
        parent::__construct($options);
        $this->linkTrack = $linkTrack;
        $this->relatedTopics = $relatedTopics;
    }

    public function getLinkTrack(): string
    {
        return $this->linkTrack;
    }

    /**
     * @return AdaClass[]
     */
    public function getRelatedTopics(): array
    {
        return $this->relatedTopics;
    }
}
