<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Map\SubPresenter;

use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;

class PromoPresenter extends Presenter
{
    /** @var Programme */
    private $programme;

    public function __construct(Programme $programme, array $options = [])
    {
        parent::__construct($options);
        $this->programme = $programme;
        //@TODO Do I need the image sizes from ProgrammeInfoPresenter here?
    }

    public function getProgramme(): Programme
    {
        return $this->programme;
    }
}
