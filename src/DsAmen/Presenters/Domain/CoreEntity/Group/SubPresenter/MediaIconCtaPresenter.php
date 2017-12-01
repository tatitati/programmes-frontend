<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Domain\CoreEntity\Group\SubPresenter;

use App\DsAmen\Presenters\Domain\CoreEntity\Base\SubPresenter\BaseCtaPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Collection;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MediaIconCtaPresenter extends BaseCtaPresenter
{
    private $additionalOptions = [
        'cta_class' => 'cta br-box-secondary',
    ];

    public function __construct(CoreEntity $coreEntity, UrlGeneratorInterface $router, array $options = [])
    {
        $options = array_merge($this->additionalOptions, $options);
        parent::__construct($coreEntity, $router, $options);
    }

    public function getMediaIconType(): string
    {
        return 'media';
    }

    public function getMediaIconName(): string
    {
        if ($this->coreEntity instanceof Collection) {
            return 'collection';
        }

        return 'image';
    }

    public function getLabelTranslation(): string
    {
        return '';
    }

    public function getUrl(): string
    {
        return $this->router->generate(
            'find_by_pid',
            ['pid' => $this->coreEntity->getPid()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function getLinkLocation(): string
    {
        return $this->getOption('link_location_prefix') . 'calltoaction';
    }
}
