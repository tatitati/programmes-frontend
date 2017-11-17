<?php
declare(strict_types=1);

namespace App\ExternalApi\Recipes\Domain;

use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;

/**
 * This class' implementation is a bit weird because we want to reuse the responsive image presenter.
 * It takes an Image object from ProgrammesPagesService, so we extend that class here. As we only
 * need the getUrl method from the base object, we pass stub values to the parent constructor and override
 * getUrl. The url is generated using the id obtained from the Recipes API, and it's not a Pid.
 */
class RecipeImage extends Image
{
    /** @var string */
    private $id;

    public function __construct(string $id)
    {
        parent::__construct(new Pid('p0000001'), '', '', '', '', 'jpg');
        $this->id = $id;
    }

    public function getUrl($width, $height = 'n'): string
    {
        $url = 'https://ichef.bbci.co.uk/food/ic/food_16x9_';
        return $url . urlencode($width) . '/recipes/' . urlencode($this->id) . '_16x9.jpg';
    }
}
