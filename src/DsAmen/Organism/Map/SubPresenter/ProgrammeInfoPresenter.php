<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Map\SubPresenter;

use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;

class ProgrammeInfoPresenter extends Presenter
{
    /** @var mixed[] */
    protected $options = [
        'is_three_column' => false,
        'show_mini_map' => false,
    ];

    /** @var array */
    private $imageSizes = [
        768 => 1 / 2,
        1008 => '625px',
        1280 => '831px',
    ];

    /** @var Programme */
    private $programme;

    /** @var bool */
    private $showMiniMap;

    public function __construct(Programme $programme, array $options = [])
    {
        parent::__construct($options);
        $this->programme = $programme;
        $this->showMiniMap = $this->getOption('show_mini_map');

        if ($this->getOption('is_three_column')) {
            $this->imageSizes[1008] = '486px';
            $this->imageSizes[1280] = '624px';
        }
    }

    public function getImagesSizes(): array
    {
        return $this->imageSizes;
    }

    public function getProgramme(): Programme
    {
        return $this->programme;
    }

    public function showMiniMap(): bool
    {
        return $this->showMiniMap;
    }
}
