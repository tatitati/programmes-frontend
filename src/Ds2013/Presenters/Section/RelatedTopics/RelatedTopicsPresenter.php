<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Section\RelatedTopics;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;

class RelatedTopicsPresenter extends Presenter
{
    /** @var null|Pid */
    private $contextPid;

    /** @var string */
    private $linkTrack;

    /** @var array AdaClass[] */
    private $relatedTopics;

    public function __construct(array $relatedTopics, string $linkTrack, Pid $contextPid = null, array $options = [])
    {
        parent::__construct($options);

        $this->contextPid = $contextPid;
        $this->linkTrack = $linkTrack;
        $this->relatedTopics = $relatedTopics;
    }

    public function getContextPid(): ?Pid
    {
        return $this->contextPid;
    }

    public function getLinkTrack(): string
    {
        return $this->linkTrack;
    }

    public function getRelatedTopics(): array
    {
        return $this->relatedTopics;
    }
}
