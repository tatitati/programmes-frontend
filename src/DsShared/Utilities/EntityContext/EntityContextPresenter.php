<?php
declare(strict_types = 1);
namespace App\DsShared\Utilities\EntityContext;

use App\DsShared\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;

class EntityContextPresenter extends Presenter
{
    /** @var CoreEntity[] */
    private $ancestry;

    public function __construct(
        CoreEntity $entity,
        array $options = []
    ) {
        parent::__construct($options);
        $this->ancestry = array_reverse($entity->getAncestry());
    }

    public function getAncestry(): array
    {
        return $this->ancestry;
    }
}
