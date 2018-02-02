<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Section\Map\SubPresenter;

use App\Exception\InvalidOptionException;

abstract class RightColumnPresenter extends ColumnPresenter
{
    /** @var mixed[] */
    protected $options = [
        'show_mini_map' => false,
    ];

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
