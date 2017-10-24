<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\CoreEntity\Base\SubPresenter;

use App\DsAmen\Presenter;
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
        'cta_class' => 'icon-cta--dark',
        'link_location_prefix' => 'programmeobject_',
    ];

    public function __construct(CoreEntity $coreEntity, UrlGeneratorInterface $router, array $options = [])
    {
        parent::__construct($options);
        $this->router = $router;
        $this->coreEntity = $coreEntity;
    }

    public function getDuration(): int
    {
        if ($this->coreEntity instanceof ProgrammeItem &&
            !($this->coreEntity instanceof Episode && $this->coreEntity->isTv())
        ) {
            return $this->coreEntity->getDuration();
        }

        return 0;
    }

    abstract public function getMediaIconName(): string;

    abstract public function getLabelTranslation(): string;

    public function getLinkLocationPrefix(): string
    {
        if ($this->coreEntity->isTv() && $this->getOption('force_iplayer_linking')) {
            return 'map_iplayer_';
        }

        return $this->getOption('link_location_prefix');
    }

    abstract public function getUrl(): string;
}
