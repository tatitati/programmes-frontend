<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Domain\ContentBlock\Image;

use App\Ds2013\Presenter;
use App\ExternalApi\Isite\Domain\ContentBlock\Image;

class ImagePresenter extends Presenter
{
    /** @var Image */
    private $imageBlock;

    /** @var bool */
    private $inPrimaryColumn;

    public function __construct(Image $imageBlock, bool $inPrimaryColumn, array $options = [])
    {
        parent::__construct($options);
        $this->imageBlock = $imageBlock;
        $this->inPrimaryColumn = $inPrimaryColumn;
    }

    public function getImageBlock(): Image
    {
        return $this->imageBlock;
    }
}
