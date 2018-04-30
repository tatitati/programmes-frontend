<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Domain\CoreEntity\Base\SubPresenter;

use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class BaseImagePresenter extends Presenter
{
    /** @var CoreEntity */
    protected $coreEntity;

    /** @var UrlGeneratorInterface */
    protected $router;

    /** @var BaseCtaPresenter */
    protected $ctaPresenter;

    /** @var array */
    protected $options = [
        'show_image' => true,
        'is_lazy_loaded' => true,
        'force_playout_linking' => false,
        'default_width' => 320,
        'sizes' => [
            // @TODO confirm these are the right sizes
            0 => '0vw',
            320 => 1 / 4,
            480 => 1 / 4,
            600 => 1 / 3,
            1008 => '336px',
            1280 => '432px',
        ],
        // classes & elements
        'media_panel_class' => '1/1',

        // badge to overlay the top of the image
        'badge_text' => '',
        'badge_class' => 'br-box-highlight',
        'cta_options' => [],
    ];

    public function __construct(
        CoreEntity $coreEntity,
        UrlGeneratorInterface $router,
        ?BaseCtaPresenter $ctaPresenter,
        array $options = []
    ) {
        parent::__construct($options);
        $this->router = $router;
        $this->coreEntity = $coreEntity;
        $this->ctaPresenter = $ctaPresenter;
    }

    public function getImage(): ?Image
    {
        if (!$this->getOption('show_image')) {
            return null;
        }

        return $this->coreEntity->getImage();
    }

    public function getCtaPresenter(): ?BaseCtaPresenter
    {
        return $this->ctaPresenter;
    }

    public function showCta(): bool
    {
        return ($this->coreEntity instanceof ProgrammeItem && $this->coreEntity->isStreamable());
    }
}
