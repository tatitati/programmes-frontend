<?php
declare(strict_types=1);

namespace App\DsAmen\Organism\Programme\SubPresenters;

use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProgrammeCtaPresenter extends Presenter
{
    /** @var ProgrammeItem */
    private $programme;

    /** @var UrlGeneratorInterface */
    private $router;

    protected $options = [
        'cta_class' => 'icon-cta--dark',
        'link_location_prefix' => 'programmeobject_',
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
            if ($this->programme->getMediaType() === MediaTypeEnum::AUDIO) {
                return 'iplayer-radio';
            }

            return 'iplayer';
        }

        return 'play';
    }

    public function getPlayTranslation(): string
    {
        if ($this->programme instanceof Episode) {
            return 'iplayer_play_episode';
        }

        return 'iplayer_play_clip';
    }

    public function getPlayerUrl(): string
    {
        $routeName = 'iplayer_play';
        $routeArguments = ['pid' => $this->programme->getPid()];

        if ($this->programme->isRadio() || $this->programme->isAudio() || $this->programme instanceof Clip) {
            $routeName = 'find_by_pid';
            $routeArguments['_fragment'] = 'play';
        }

        return $this->router->generate($routeName, $routeArguments, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
