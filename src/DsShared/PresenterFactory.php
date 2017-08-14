<?php
declare(strict_types = 1);

namespace App\DsShared;

use App\DsShared\Molecule\Image\ImagePresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Image;

/**
 * DsShared Factory Class for creating presenters.
 */
class PresenterFactory
{
    public function imagePresenter(
        Image $image,
        int $defaultWidth,
        $sizes,
        array $options = []
    ): ImagePresenter {
        return new ImagePresenter(
            $image,
            $defaultWidth,
            $sizes,
            $options
        );
    }
}
