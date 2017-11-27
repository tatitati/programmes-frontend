<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\CoreEntity\Base\SubPresenter;

use App\DsAmen\Presenter;
use App\Exception\InvalidOptionException;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class BaseCtaPresenter extends Presenter
{
    /** @var CoreEntity */
    protected $coreEntity;

    /** @var UrlGeneratorInterface */
    protected $router;

    /** @var array */
    protected $options = [
        'cta_class' => 'cta cta--dark',
        'link_location_prefix' => 'programmeobject_',
    ];

    public function __construct(CoreEntity $coreEntity, UrlGeneratorInterface $router, array $options = [])
    {
        parent::__construct($options);
        $this->router = $router;
        $this->coreEntity = $coreEntity;
    }

    abstract public function getMediaIconType(): string;

    abstract public function getMediaIconName(): string;

    abstract public function getLabelTranslation(): string;

    abstract public function getUrl(): string;

    abstract public function getLinkLocation(): string;

    public function getTemplateVariableName(): string
    {
        return 'cta';
    }
}
