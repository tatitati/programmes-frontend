<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Map\SubPresenter;

use App\DsAmen\Presenter;
use App\Exception\InvalidOptionException;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;

abstract class RightColumnPresenter extends Presenter
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

    /** @var mixed[] */
    protected $options = [
        'show_mini_map' => false,
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

    public function showMiniMap(): bool
    {
        return $this->getOption('show_mini_map');
    }

    protected function validateOptions(array $options): void
    {
        parent::validateOptions($options);

        if (!is_bool($options['show_mini_map'])) {
            throw new InvalidOptionException('show_mini_map option must be a boolean');
        }
    }
}
