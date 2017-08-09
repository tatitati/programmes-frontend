<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Map\SubPresenter;

use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;

class ProgrammeInfoPresenter extends Presenter
{
    /** @var array */
    private $imageSizes = [
        768 => 1 / 2,
        1008 => '625px',
        1280 => '831px',
    ];
    /** @var Programme */
    private $programme;

    public function __construct(Programme $programme, array $options = [])
    {
        parent::__construct($options);
        $this->programme = $programme;

        if ($this->getOption('coming_soon_takeover') || $this->getOption('world_news')) {
            $this->imageSizes[1008] = '486px';
            $this->imageSizes[1280] = '624px';
        } elseif ($this->getOption('must_show_tx_column')) {
            if ($this->getOption('show_mini_map')) {
                $this->imageSizes[1008] = '323px';
                $this->imageSizes[1280] = '414px';
            } else {
                $this->imageSizes[1008] = '486px';
                $this->imageSizes[1280] = '624px';
            }
        }
    }

    public function getProgramme(): Programme
    {
        return $this->programme;
    }
}
