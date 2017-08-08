<?php
declare(strict_types = 1);

namespace App\ValueObject;

use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
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

    /** @var string */
    private $schemaType = '';

    public function __construct($context = null, string $canonicalUrl = '')
    {
        $this->canonicalUrl = $canonicalUrl;

        if ($context instanceof CoreEntity) {
            $this->description = $context->getShortSynopsis();
            $this->image = $context->getImage();
            $this->isRadio = $context->isRadio();
            $this->titlePrefix = $this->coreEntityTitlePrefix($context);
            $this->schemaType = $this->getSchemaTypeEquivalent($context);
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

    public function getRdfaAttributes(): string
    {
        if (!$this->schemaType) {
            return '';
        }

        return 'vocab="http://schema.org/" typeof=' . $this->schemaType;
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

    private function getSchemaTypeEquivalent(CoreEntity $entity)
    {
        $type = $entity->isRadio() ? 'Radio' : 'TV';

        if ($entity instanceof Episode) {
            return $type . 'Episode';
        }

        if ($entity instanceof Series) {
            return $type . 'Season';
        }

        if ($entity instanceof Brand) {
            return $type . 'Series';
        }

        if ($entity instanceof Clip) {
            return $type . 'Clip';
        }

        return '';
    }
}
