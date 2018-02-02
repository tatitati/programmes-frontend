<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Section\Map\SubPresenter;

use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;

abstract class ColumnPresenter extends Presenter
{
    /** @var int */
    protected $defaultImageSize = 240;

    /** @var mixed[] */
    protected $imageSizes = [
        320 => 1/2,
        768 => 1/4,
        1008 => '242px',
        1280 => '310px',
    ];

    /** @var ProgrammeContainer */
    protected $programmeContainer;

    public function __construct(ProgrammeContainer $programmeContainer, array $options = [])
    {
        parent::__construct($options);

        $this->programmeContainer = $programmeContainer;
    }

    public function getDefaultImageSize(): int
    {
        return $this->defaultImageSize;
    }

    public function getImageSizes(): array
    {
        return $this->imageSizes;
    }

    public function getProgrammeContainer(): ProgrammeContainer
    {
        return $this->programmeContainer;
    }
}
