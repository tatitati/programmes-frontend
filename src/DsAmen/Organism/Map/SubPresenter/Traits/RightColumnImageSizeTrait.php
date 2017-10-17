<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Map\SubPresenter\Traits;

trait RightColumnImageSizeTrait
{
    public function getImageSizes(): array
    {
        if (isset($this->options['full_width']) && $this->options['full_width']) {
            return [768 => 1/3, 1008 => '324px', 1280 => '414px'];
        }
        return [320 => 1/2, 768 => 1/4, 1008 => '242px', 1280 => '310px'];
    }

    public function getDefaultImageSize(): int
    {
        return (isset($this->options['full_width']) && $this->options['full_width']) ? 320 : 240;
    }
}
