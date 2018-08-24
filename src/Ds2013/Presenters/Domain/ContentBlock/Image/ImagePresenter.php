<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Domain\ContentBlock\Image;

use App\Ds2013\Presenter;
use App\ExternalApi\Isite\Domain\ContentBlock\Image;

class ImagePresenter extends Presenter
{
    /** @var Image */
    private $imageBlock;

    public function __construct(Image $imageBlock, array $options = [])
    {
        parent::__construct($options);
        $this->imageBlock = $imageBlock;
    }

    public function getImageBlock(): Image
    {
        return $this->imageBlock;
    }
}
