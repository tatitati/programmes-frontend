<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Section\Map\SubPresenter;

use App\Exception\InvalidOptionException;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;

class ProgrammeInfoPresenter extends LeftColumnPresenter
{
    /** @var mixed[] */
    protected $options = [
        'is_three_column' => false,
        'show_mini_map' => false,
    ];

    /** @var bool */
    private $showMiniMap;

    public function __construct(ProgrammeContainer $programme, array $options = [])
    {
        parent::__construct($programme, $options);

        $this->showMiniMap = $this->getOption('show_mini_map');
    }

    public function showMiniMap(): bool
    {
        return $this->showMiniMap;
    }

    protected function validateOptions(array $options): void
    {
        parent::validateOptions($options);

        if (!is_bool($options['show_mini_map'])) {
            throw new InvalidOptionException('show_mini_map option must be a boolean');
        }
    }
}
