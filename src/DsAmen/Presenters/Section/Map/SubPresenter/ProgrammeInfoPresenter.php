<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Section\Map\SubPresenter;

use App\DsAmen\Presenters\Section\Map\SubPresenter\Traits\LeftColumnImageSizeTrait;
use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;

class ProgrammeInfoPresenter extends Presenter
{
    use LeftColumnImageSizeTrait;

    /** @var mixed[] */
    protected $options = [
        'is_three_column' => false,
        'show_mini_map' => false,
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
