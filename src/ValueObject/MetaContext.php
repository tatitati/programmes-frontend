<?php
declare(strict_types = 1);

namespace App\ValueObject;

use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
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

    /** @var string */
    private $canonicalUrl = '';

    /** @var bool */
    private $showAdverts = false;

    /** @var CoreEntity|Network */
    private $context;

    public function __construct($context = null, string $canonicalUrl = '')
    {
        $this->canonicalUrl = $canonicalUrl;
        $this->context = $context;

        if ($context instanceof CoreEntity) {
            $this->description = $context->getShortSynopsis();
            $this->image = $context->getImage();
            $this->isRadio = $context->isRadio();
            $this->titlePrefix = $this->coreEntityTitlePrefix($context);

            if ($context->getNetwork()) {
                $this->showAdverts = $context->getNetwork()->isInternational();
            }
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

    public function context()
    {
        return $this->context;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function hasSchemaType(): bool
    {
        return $this->context instanceof Programme;
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

    public function showAdverts(): bool
    {
        return $this->showAdverts;
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
