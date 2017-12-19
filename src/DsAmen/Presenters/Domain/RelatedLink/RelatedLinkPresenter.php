<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Domain\RelatedLink;

use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\RelatedLink;

class RelatedLinkPresenter extends Presenter
{
    /** @var RelatedLink */
    private $relatedLink;

    public function __construct(RelatedLink $relatedLink, array $options = [])
    {
        $this->relatedLink = $relatedLink;
        parent::__construct($options);
    }

    public function getLink(): string
    {
        return $this->relatedLink->getUri();
    }

    public function getTitle(): string
    {
        return $this->relatedLink->getTitle();
    }
}
