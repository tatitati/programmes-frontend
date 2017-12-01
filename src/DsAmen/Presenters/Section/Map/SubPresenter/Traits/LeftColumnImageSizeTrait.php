<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Section\Map\SubPresenter\Traits;

trait LeftColumnImageSizeTrait
{
    public function getImageSizes(): array
    {
        if ($this->options['is_three_column']) {
            return [768 => 1 / 2, 1008 => '486px', 1280 => '624px'];
        }
        return [768 => 1 / 2, 1008 => '625px', 1280 => '831px'];
    }

    public function getDefaultImageSize(): int
    {
        if ($this->options['is_three_column']) {
            return 480;
        }
        return 624;
    }
}
