<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\CoreEntity\CollapsedBroadcast\SubPresenter;

use App\DsAmen\Organism\CoreEntity\Base\SubPresenter\BaseCtaPresenter;
use App\DsShared\Helpers\LiveBroadcastHelper;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CtaPresenter extends BaseCtaPresenter
{
    /** @var  CollapsedBroadcast */
    protected $collapsedBroadcast;

    /** @var LiveBroadcastHelper */
    protected $liveBroadcastHelper;

    protected $additionalOptions = [
        'link_to_start' => false,
        'link_location_suffix' => '',

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
        return $this->getOption('link_location_prefix') . 'calltoaction' . $this->getOption('link_location_suffix');
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

    public function showLiveMessage(): bool
    {
        return $this->liveBroadcastHelper->isWatchableLive($this->collapsedBroadcast);
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
        if ($this->liveBroadcastHelper->isWatchableLive($this->collapsedBroadcast, true)) {
            if ($this->getOption('link_to_start') && $this->collapsedBroadcast->getProgrammeItem()->isVideo()) {
                return $this->liveBroadcastHelper->simulcastUrl(
                    $this->collapsedBroadcast,
                    null,
                    ['rewindTo' => 'current']
                );
            }

            return $this->liveBroadcastHelper->simulcastUrl($this->collapsedBroadcast);
        }

        if ($this->coreEntity instanceof Episode && $this->coreEntity->isVideo()) {
            return $this->router->generate(
                'iplayer_play',
                ['pid' => $this->coreEntity->getPid()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        return $this->router->generate(
            'find_by_pid',
            ['pid' => $this->coreEntity->getPid(), '_fragment' => $this->coreEntity instanceof Episode ? 'play' : ''],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    protected function validateOptions(array $options): void
    {
        parent::validateOptions($options);

        if (!is_bool($options['link_to_start'])) {
            throw new InvalidOptionException('link_to_start option must be a boolean');
        }
    }
}
