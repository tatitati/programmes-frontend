<?php
declare(strict_types = 1);

namespace App\ValueObject;

use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;

class MetaContext
{
    /** @var string */
    private $description = '';

    /** @var Image */
    private $image;

    /** @var bool */
    private $isRadio = false;

    /** @var string */
    private $titlePrefix = '';

    public function __construct($context = null)
    {
        if ($context instanceof Programme) {
            $this->description = $context->getShortSynopsis();
            $this->image = $context->getImage();
            $this->isRadio = $context->isRadio();
            if ($context->getNetwork()) {
                $this->titlePrefix = $context->getNetwork()->getName();
            }

            // TODO add rdfa type so that it can be added onto the <body> tag
            // e.g. http://www.bbc.co.uk/programmes/b006q2x0 has
            // <body vocab="http://schema.org/" typeof="TVSeries">
        } elseif ($context instanceof Service) {
            $this->isRadio = $context->isRadio();
            $this->titlePrefix = $context->getName();
            if ($context->getNetwork()) {
                $this->image = $context->getNetwork()->getImage();
            }
        }

        if (is_null($this->image)) {
            $this->image = new Image(
                new Pid('p01tqv8z'),
                'bbc_640x360.png',
                'BBC Blocks for /programmes',
                'BBC Blocks for /programmes',
                'standard',
                'png'
            );
        }
    }

    public function description(): string
    {
        return $this->description;
    }

    public function image(): Image
    {
        return $this->image;
    }

    public function isRadio(): bool
    {
        return $this->isRadio;
    }

    public function titlePrefix(): string
    {
        return $this->titlePrefix;
    }
}
