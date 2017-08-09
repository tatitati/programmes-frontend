<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Map\SubPresenter;

use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;

class OnDemandPresenter extends Presenter
{
    /** @var Programme */
    private $programme;

    private $class = '1/1';

    public function __construct(Programme $programme, array $options = [])
    {
        parent::__construct($options);
        $this->programme = $programme;

        if ($this->getOption('must_show_tx_column')) {
            $this->class = '1/2@gel1b';
        }
    }

    public function getProgramme(): Programme
    {
        return $this->programme;
    }

    public function getClass(): string
    {
        return $this->class;
    }
}
