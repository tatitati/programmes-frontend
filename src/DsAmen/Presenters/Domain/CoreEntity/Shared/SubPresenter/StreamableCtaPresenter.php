<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Domain\CoreEntity\Shared\SubPresenter;

use App\DsAmen\Presenters\Domain\CoreEntity\Base\SubPresenter\BaseCtaPresenter;
use App\DsShared\Helpers\StreamableHelper;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StreamableCtaPresenter extends BaseCtaPresenter
{
    protected $additionalOptions = [
        'show_duration' => true,
    ];
    /** @var StreamableHelper */
    private $streamableHelper;

    public function __construct(StreamableHelper $streamableHelper, CoreEntity $coreEntity, UrlGeneratorInterface $router, array $options = [])
    {
        $options = array_merge($this->additionalOptions, $options);
        parent::__construct($coreEntity, $router, $options);
        $this->streamableHelper = $streamableHelper;
    }

    public function getMediaIconName(): string
    {
        if ($this->coreEntity instanceof Episode) {
            if ($this->streamableHelper->shouldTreatProgrammeItemAsAudio($this->coreEntity)) {
                return 'iplayer-radio';
            }

            return 'iplayer';
        }

        return 'play';
    }

    public function getMediaIconType(): string
    {
        return 'audio-visual';
    }

    public function getLinkLocation(): string
    {
        if ($this->streamableHelper->shouldStreamViaIplayer($this->coreEntity) && $this->getOption('force_iplayer_linking')) {
            return 'map_iplayer_calltoaction';
        }

        return $this->getOption('link_location_prefix') . 'calltoaction';
    }


    public function getLabelTranslation(): string
    {
        return '';
    }

    public function getUrl(): string
    {
        return $this->router->generate(
            $this->streamableHelper->getRouteForProgrammeItem($this->coreEntity),
            ['pid' => $this->coreEntity->getPid()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function getDuration(): int
    {
        if ($this->showDuration()) {
            return $this->coreEntity->getDuration();
        }

        return 0;
    }

    private function showDuration(): bool
    {
        $isTvEpisode = ($this->coreEntity instanceof Episode && $this->coreEntity->isVideo());
        $isProgrammeItem = ($this->coreEntity instanceof ProgrammeItem);
        return ($isProgrammeItem && !$isTvEpisode && $this->getOption('show_duration') && $this->coreEntity->getDuration());
    }
}
