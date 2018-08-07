<?php
declare(strict_types = 1);
namespace App\Ds2013\Presenters\Domain\CoreEntity\Programme\SubPresenters;

use App\Ds2013\Presenters\Domain\CoreEntity\Programme\ProgrammePresenterBase;
use App\DsShared\Helpers\PlayTranslationsHelper;
use App\DsShared\Helpers\StreamableHelper;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Sub-presenter for ProgrammePresenter
 */
class ProgrammeOverlayPresenter extends ProgrammePresenterBase
{
    // @TODO see if this default_width and sizes make sense
    /** @var array */
    protected $options = [
        'show_image' => true,
        'show_overlay' => true,
        'is_lazy_loaded' => true,
        'show_standalone_cta' => false,
        'classes' => '1/4@bpb1 1/4@bpb2 1/3@bpw',
        'default_width' => 320,
        'cta_link_location_track' => 'programmeobjectlink=cta',
        'sizes' => [
            0 => '0vw',
            320 => 1 / 4,
            480 => 1 / 4,
            600 => 1 / 3,
            // @TODO confirm these are the right sizes
            1008 => '336px',
            1280 => '432px',
        ],
    ];

    /** @var PlayTranslationsHelper */
    protected $playTranslationsHelper;

    /** @var StreamableHelper */
    protected $streamUrlHelper;

    public function __construct(
        UrlGeneratorInterface $router,
        PlayTranslationsHelper $playTranslationsHelper,
        StreamableHelper $streamUrlHelper,
        Programme $programme,
        array $options = []
    ) {
        parent::__construct($router, $programme, $options);
        $this->playTranslationsHelper = $playTranslationsHelper;
        $this->streamUrlHelper = $streamUrlHelper;
    }

    public function getAvailabilityInWords(): string
    {
        if ($this->programme instanceof ProgrammeItem && $this->programme->isStreamable()) {
            // @TODO allow override services?
            return $this->playTranslationsHelper->translateAvailableUntilToWords($this->programme);
        }
        return '';
    }

    public function getImage(): Image
    {
        return $this->programme->getImage();
    }

    public function getImageUrl(int $xSize, int $ySize): string
    {
        return $this->programme->getImage()->getUrl($xSize, $ySize);
    }

    public function getMediaIconName(): string
    {
        if ($this->streamUrlHelper->shouldTreatProgrammeItemAsAudio($this->programme)) {
            return 'listen';
        }

        return $this->programme instanceof Episode ? 'iplayer' : 'play';
    }

    public function getPlaybackUrl(): string
    {
        if (!$this->programme instanceof ProgrammeItem) {
            return '';
        }

        $routeName = $this->streamUrlHelper->getRouteForProgrammeItem($this->programme);
        $routeArguments = ['pid' => $this->programme->getPid()];

        if ($routeName === 'find_by_pid') {
            // Clip or Radio programme. Link to programme page for now.
            $routeArguments['_fragment'] = 'play';
        }

        return $this->router->generate($routeName, $routeArguments, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function getOverlayDivClasses()
    {
        if ($this->getOption('show_standalone_cta')) {
            return [];
        }
        return [
            'programme__overlay' => true,
            'programme__overlay--available' => $this->isAvailable(),
        ];
    }
}
