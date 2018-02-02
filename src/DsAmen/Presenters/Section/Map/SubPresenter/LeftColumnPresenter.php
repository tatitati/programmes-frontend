<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Section\Map\SubPresenter;

use App\Exception\InvalidOptionException;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;

abstract class LeftColumnPresenter extends ColumnPresenter
{
    /** @var mixed[] */
    protected $options = [
        'is_three_column' => false,
    ];

    public function __construct(ProgrammeContainer $programmeContainer, array $options = [])
    {
        parent::__construct($programmeContainer, $options);

        if ($this->options['is_three_column']) {
            $this->defaultImageSize = 480;
            $this->imageSizes = [768 => 1 / 2, 1008 => '486px', 1280 => '624px'];
        } else {
            $this->defaultImageSize = 624;
            $this->imageSizes = [768 => 1 / 2, 1008 => '625px', 1280 => '831px'];
        }
    }

    protected function validateOptions(array $options): void
    {
        parent::validateOptions($options);

        if (!is_bool($options['is_three_column'])) {
            throw new InvalidOptionException('is_three_column option must be a boolean');
        }
    }
}
