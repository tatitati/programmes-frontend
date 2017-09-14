<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Map\SubPresenter;

use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;

/**
 * Tx means Transmission
 */
class TxPresenter extends Presenter
{
    /** @var Programme */
    private $programme;

    public function __construct(Programme $programme, array $options = [])
    {
        parent::__construct($options);
        $this->programme = $programme;
    }

    public function getProgramme(): Programme
    {
        return $this->programme;
    }
}
