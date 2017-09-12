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
