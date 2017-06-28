<?php
declare(strict_types = 1);
namespace App\Ds2013\Organism\Programme\SubPresenters;

use App\Ds2013\Helpers\PlayTranslationsHelper;
use App\Ds2013\Organism\Programme\ProgrammePresenterBase;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ProgrammeImagePresenter
 *
 * Sub-presenter for ProgrammePresenter
 *
 * @package App\Ds2013\Organism\Programme\ProgrammeImage
 */
class ProgrammeImagePresenter extends ProgrammePresenterBase
{
    /** @var array */
    protected $options = [
        'show_image' => true,
        'show_overlay' => true,
        'is_lazy_loaded' => true,
        'classes' => '1/4@bpb1 1/4@bpb2 1/3@bpw',
        'sizes' => [
            0 => '0vw',
            320 => 1 / 4,
            480 => 1 / 4,
            600 => 1 / 3,
        ],
    ];

    /** @var PlayTranslationsHelper */
    protected $playTranslationsHelper;

    public function __construct(
        UrlGeneratorInterface $router,
        PlayTranslationsHelper $playTranslationsHelper,
        Programme $programme,
        array $options = []
    ) {
        parent::__construct($router, $programme, $options);
        $this->playTranslationsHelper = $playTranslationsHelper;
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
        if ($this->programme->isRadio()) {
            return 'iplayer-radio';
        }
        return 'iplayer';
    }

    public function getPlaybackUrl(): string
    {
        if (!$this->programme instanceof ProgrammeItem) {
            return '';
        }

        if ($this->programme->isRadio() || $this->programme->isAudio() || $this->programme instanceof Clip) {
            // Radio programme. Link to programme page for now.
            return $this->router->generate('find_by_pid', ['pid' => $this->programme->getPid()]) . '#play';
        }
        // TV Episode @TODO this needs to be more sophistimicated
        return '/iplayer/' . urlencode((string) $this->programme->getPid());
    }
}
