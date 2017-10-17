<?php
declare(strict_types=1);

namespace App\DsAmen\Organism\Programme\CollapsedBroadcastSubPresenters;

use App\DsAmen\Organism\Programme\SubPresenters\ProgrammeCtaPresenter;
use App\DsShared\Helpers\LiveBroadcastHelper;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CollapsedBroadcastProgrammeCtaPresenter extends ProgrammeCtaPresenter
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

        if ($this->programme->isAudio()) {
            return 'iplayer_listen_live';
        }

        return 'iplayer_watch_live';
    }

    public function getMediaIconName(): string
    {
        if ($this->getOption('link_to_start')) {
            return 'live-restart';
        }

        return parent::getMediaIconName();
    }

    public function getTemplateVariableName(): string
    {
        return 'collapsed_broadcast_cta';
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

        return parent::getUrl();
    }

    protected function validateOptions(array $options): void
    {
        parent::validateOptions($options);

        if (!is_bool($options['link_to_start'])) {
            throw new InvalidOptionException('link_to_start option must be a boolean');
        }
    }
}
