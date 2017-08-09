<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Map\SubPresenter;

use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;

class ComingSoonPresenter extends Presenter
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

    public function getComingSoon()
    {
        return false;
    }

    public function getComingSoonTextOnly()
    {
        return 'I Я On Coming Soon for ' . $this->programme->getTitle();
    }
}
