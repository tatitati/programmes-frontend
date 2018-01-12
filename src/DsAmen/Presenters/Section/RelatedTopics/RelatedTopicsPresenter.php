<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Section\RelatedTopics;

use App\DsAmen\Presenter;
use App\ExternalApi\Ada\Domain\AdaClass;

class RelatedTopicsPresenter extends Presenter
{
    /** @var AdaClass[] */
    private $adaClasses;

    public function __construct(array $adaClasses, array $options = [])
    {
        parent::__construct($options);
        $this->adaClasses = $adaClasses;
    }

    /** @return AdaClass[] */
    public function getAdaClasses(): array
    {
        return $this->adaClasses;
    }
}
