<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Utilities\Cta;

use App\Ds2013\Presenter;
use App\DsShared\Helpers\PlayTranslationsHelper;
use App\DsShared\Helpers\StreamableHelper;
use App\Exception\InvalidOptionException;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Gallery;
use BBC\ProgrammesPagesService\Domain\Entity\Group;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use Exception;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Future Improvements:
 * Expand functionality to include MAP Playout CTA
 * Expand functionality to include AMEN CTAs
 * Possibly using branding colours for CTAs (this will require passing classes in to the presenter)
 */
class CtaPresenter extends Presenter
{
    protected $options = [
        'is_overlay' => true, // Does the CTA overlay an image?
        'data_link_track' => "programmeobjectlink=cta",
    ];

    /** @var CoreEntity */
    private $coreEntity;

    /** @var PlayTranslationsHelper */
    protected $playTranslationsHelper;

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var StreamableHelper */
    private $streamUrlHelper;

    public function __construct(
        CoreEntity $coreEntity,
        PlayTranslationsHelper $playTranslationsHelper,
        UrlGeneratorInterface $router,
        StreamableHelper $streamUrlHelper,
        array $options = []
    ) {
        $this->coreEntity = $coreEntity;

        parent::__construct($options);

        $this->streamUrlHelper = $streamUrlHelper;
        $this->playTranslationsHelper = $playTranslationsHelper;
        $this->router = $router;
    }

    public function getAvailabilityInWords(): string
    {
        if (!$this->shouldIncludeLink()) {
            throw new Exception('This CTA doesn\t need a link, why are you calling this method?');
        }

        return $this->playTranslationsHelper->translateAvailableUntilToWords($this->coreEntity);
    }

    public function getMediaIconName(): string
    {
        if (!$this->coreEntity instanceof ProgrammeItem) {
            if ($this->coreEntity instanceof Gallery) {
                return 'image';
            }

            return 'collection';
        }
        if ($this->programmeIsAudio()) {
            return 'listen';
        }

        return $this->coreEntity instanceof Episode ? 'iplayer' : 'play';
    }

    public function getProductClasses()
    {
        if (!$this->coreEntity instanceof ProgrammeItem) {
            return '';
        }

        // Incase we want Sounds branding e.g. sounds-icon in the future
        if ($this->programmeIsAudio()) {
            return '';
//            return 'sounds-icon';
        }

        return $this->coreEntity instanceof Episode ? 'iplayer-icon' : '';
    }

    public function getGelIconSet()
    {
        if ($this->coreEntity instanceof ProgrammeItem) {
            return 'audio-visual';
        }

        return 'media';
    }

    public function getUrl()
    {
        if (!$this->shouldIncludeLink()) {
            throw new Exception('This CTA doesn\t need a link, why are you calling this method?');
        }

        $routeName = $this->streamUrlHelper->getRouteForProgrammeItem($this->coreEntity);
        $routeArguments = ['pid' => $this->coreEntity->getPid()];

        if ($routeName === 'find_by_pid') {
            // Clip or Radio programme. Link to programme page for now.
            $routeArguments['_fragment'] = 'play';
        }

        return $this->router->generate($routeName, $routeArguments, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function programmeIsAudio(): bool
    {
        return $this->streamUrlHelper->shouldTreatProgrammeItemAsAudio($this->coreEntity);
    }

    public function shouldIncludeLink(): bool
    {
        return $this->coreEntity instanceof Episode;
    }

    protected function validateOptions(array $options): void
    {
        if (!\is_bool($options['is_overlay'])) {
            throw new InvalidOptionException('is_overlay option must be a boolean');
        }

        if (!$options['is_overlay'] && !$this->shouldIncludeLink()) {
            throw new InvalidOptionException('Only Episodes can be non-overlays');
        }
    }
}
