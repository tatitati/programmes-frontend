<?php
declare(strict_types = 1);

namespace App\ValueObject;

use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
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

    /** @var string */
    private $canonicalUrl = '';

    public function __construct($context = null, string $canonicalUrl = '')
    {
        $this->canonicalUrl = $canonicalUrl;

        if ($context instanceof CoreEntity) {
            $this->description = $context->getShortSynopsis();
            $this->image = $context->getImage();
            $this->isRadio = $context->isRadio();
            $this->titlePrefix = $this->coreEntityTitlePrefix($context);

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

    public function canonicalUrl(): string
    {
        return $this->canonicalUrl;
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

    private function coreEntityTitlePrefix(CoreEntity $coreEntity): string
    {
        $prefix = '';
        if ($coreEntity->getTleo()->getNetwork()) {
            $prefix = $coreEntity->getTleo()->getNetwork()->getName() . ' - ';
        }

        $longerTitleParts = [];
        foreach (array_reverse($coreEntity->getAncestry()) as $ancestor) {
            $longerTitleParts[] = $ancestor->getTitle();
        }

        $prefix .= implode(', ', $longerTitleParts);
        return $prefix;
    }
}
