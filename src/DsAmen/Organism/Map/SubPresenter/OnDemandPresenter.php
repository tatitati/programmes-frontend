<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Map\SubPresenter;

use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;

class OnDemandPresenter extends Presenter
{
    /** @var mixed[] */
    protected $options = [
        'full_width' => false, // The full width of the right hand MAP column
    ];

    /** @var string */
    private $class = '1/2@gel1b';

    /** @var Programme */
    private $programme;

    public function __construct(Programme $programme, array $options = [])
    {
        parent::__construct($options);
        $this->programme = $programme;

        if ($this->getOption('full_width')) {
            $this->class = '1/1';
        }
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getProgramme(): Programme
    {
        return $this->programme;
    }
}
