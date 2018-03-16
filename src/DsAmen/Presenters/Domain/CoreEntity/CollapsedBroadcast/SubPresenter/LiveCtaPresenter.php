<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Domain\CoreEntity\CollapsedBroadcast\SubPresenter;

use App\DsAmen\Presenters\Domain\CoreEntity\Base\SubPresenter\BaseCtaPresenter;
use App\DsShared\Helpers\LiveBroadcastHelper;
use App\Exception\InvalidOptionException;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LiveCtaPresenter extends BaseCtaPresenter
{
    /** @var  CollapsedBroadcast */
    protected $collapsedBroadcast;

    /** @var LiveBroadcastHelper */
    protected $liveBroadcastHelper;

    protected $additionalOptions = [
        'link_to_start' => false,
    ];

    public function __construct(
        CollapsedBroadcast $collapsedBroadcast,
        UrlGeneratorInterface $router,
        LiveBroadcastHelper $liveBroadcastHelper,
        array $options = []
    ) {
        $this->collapsedBroadcast = $collapsedBroadcast;
        $this->liveBroadcastHelper = $liveBroadcastHelper;
        $options = array_merge($this->additionalOptions, $options);
        parent::__construct($collapsedBroadcast->getProgrammeItem(), $router, $options);
    }

    public function getLabelTranslation(): string
    {
        if ($this->getOption('link_to_start')) {
            return 'iplayer_watch_from_start';
        }

        if ($this->coreEntity instanceof ProgrammeItem && $this->coreEntity->isAudio()) {
            return 'iplayer_listen_live';
        }

        return 'iplayer_watch_live';
    }

    public function getLinkLocation(): string
    {
        $linkLocation = $this->getOption('link_location_prefix') . 'calltoaction';
        if ($this->getOption('link_to_start')) {
            $linkLocation .= '_start';
        }
        return $linkLocation;
    }

    public function getMediaIconName(): string
    {
        if ($this->getOption('link_to_start')) {
            return 'live-restart';
        }

        if ($this->coreEntity instanceof Episode) {
            if ($this->coreEntity->isAudio()) {
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

    public function getBackgroundClass(): string
    {
        // We only remove the background for the 'watch from start cta'
        if (!$this->getOption('show_image')) {
            return 'icon--remove-background';
        }

        return '';
    }

    public function getUrl(): string
    {
        if ($this->getOption('link_to_start') && $this->collapsedBroadcast->getProgrammeItem()->isVideo()) {
            return $this->liveBroadcastHelper->simulcastUrl(
                $this->collapsedBroadcast,
                null,
                ['rewindTo' => 'current']
            );
        }
        return $this->liveBroadcastHelper->simulcastUrl($this->collapsedBroadcast);
    }

    protected function validateOptions(array $options): void
    {
        parent::validateOptions($options);

        if (!is_bool($options['link_to_start'])) {
            throw new InvalidOptionException('link_to_start option must be a boolean');
        }
    }
}
