<?php
declare(strict_types=1);

namespace App\DsAmen\Organism\Programme\SubPresenters;

use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProgrammeCtaPresenter extends Presenter
{
    /** @var ProgrammeItem */
    protected $programme;

    /** @var UrlGeneratorInterface */
    protected $router;

    protected $options = [
        'cta_class' => 'icon-cta--dark',
        'link_location_prefix' => 'programmeobject_',
        'force_iplayer_linking' => false,
    ];

    public function __construct(ProgrammeItem $programme, UrlGeneratorInterface $router, array $options = [])
    {
        parent::__construct($options);
        $this->router = $router;
        $this->programme = $programme;
    }

    public function getDuration(): int
    {
        if ($this->programme instanceof ProgrammeItem &&
            !($this->programme instanceof Episode && $this->programme->isTv())
        ) {
            return $this->programme->getDuration();
        }

        return 0;
    }

    public function getLinkLocationPrefix(): string
    {
        if ($this->getOption('force_iplayer_linking')) {
            return 'map_iplayer_';
        }

        return $this->getOption('link_location_prefix');
    }

    public function getMediaIconName(): string
    {
        if ($this->programme instanceof Episode) {
            if ($this->programme->isAudio()) {
                return 'iplayer-radio';
            }

            return 'iplayer';
        }

        return 'play';
    }

    public function getLabelTranslation(): string
    {
        if ($this->programme instanceof Episode) {
            return 'iplayer_play_episode';
        }

        return 'iplayer_play_clip';
    }

    public function getUrl(): string
    {
        if ($this->programme instanceof Episode && $this->programme->isVideo()) {
            return $this->router->generate(
                'iplayer_play',
                ['pid' => $this->programme->getPid()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        return $this->router->generate(
            'find_by_pid',
            ['pid' => $this->programme->getPid(), '_fragment' => $this->programme instanceof Episode ? 'play' : ''],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
