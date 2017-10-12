<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Programme\SubPresenters;

use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Image;

class ProgrammeImagePresenter extends Presenter
{
    /** @var Programme */
    private $programme;

    /** @var ProgrammeCtaPresenter */
    private $programmeCtaPresenter;

    protected $options = [
        'show_image' => true,
        'is_lazy_loaded' => true,
        'force_iplayer_linking' => false,
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
    ];

    public function __construct(
        Programme $programme,
        ?ProgrammeCtaPresenter $programmeCtaPresenter,
        array $options = []
    ) {
        parent::__construct($options);
        $this->programme = $programme;
        $this->programmeCtaPresenter = $programmeCtaPresenter;
    }

    public function getImage(): ?Image
    {
        if (!$this->getOption('show_image')) {
            return null;
        }

        return $this->programme->getImage();
    }

    public function getProgrammeCtaPresenter(): ?ProgrammeCtaPresenter
    {
        return $this->programmeCtaPresenter;
    }
}
