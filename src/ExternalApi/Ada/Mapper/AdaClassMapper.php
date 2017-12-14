<?php
declare(strict_types=1);

namespace App\ExternalApi\Ada\Mapper;

use App\ExternalApi\Ada\Domain\AdaClass;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;

class AdaClassMapper
{
    public function mapItem(array $adaClass): AdaClass
    {
        return new AdaClass(
            $adaClass['id'],
            $adaClass['title'],
            $adaClass['programme_items_count'],
            $this->getImageModel($adaClass['image'])
        );
    }

    /**
     * Ada Provides an ImageChef programme image recipe. Turn that into
     * a standard image representation
     */
    private function getImageModel(string $imageRecipe): Image
    {
        $parts = explode('/', $imageRecipe);
        [$pid, $extension] = explode('.', end($parts));

        return new Image(new Pid($pid), '', '', '', '', $extension);
    }
}
