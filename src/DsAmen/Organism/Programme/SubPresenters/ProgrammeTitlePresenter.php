<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Programme\SubPresenters;

use App\DsAmen\Presenter;
use App\DsShared\Helpers\TitleLogicHelper;
use App\Exception\InvalidOptionException;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProgrammeTitlePresenter extends Presenter
{
    /** @var UrlGeneratorInterface */
    protected $router;

    /** @var TitleLogicHelper */
    private $titleHelper;

    /** @var Programme */
    private $programme;

    /** @var Programme */
    private $mainTitleProgramme;

    /** @var Programme[] */
    private $subTitlesProgrammes;

    /** @var array */
    protected $options = [
        'h_tag' => 'h4',
        'text_colour_on_title_link' => true,
        'title_format' => 'item::ancestry',
        'link_location_prefix' => 'programmeobject_',
        'title_size_large' => 'gel-pica-bold',
        'title_size_small' => 'gel-pica',
    ];

    public function __construct(
        Programme $programme,
        UrlGeneratorInterface $router,
        TitleLogicHelper $titleHelper,
        array $options = []
    ) {
        parent::__construct($options);

        $this->router = $router;
        $this->titleHelper = $titleHelper;
        $this->programme = $programme;
    }

    public function getBrandingClass(): string
    {
        if (!$this->getOption('branding_name') || !$this->getOption('text_colour_on_title_link')) {
            return '';
        }

        return 'br-' . $this->getOption('branding_name') . '-text-ontext';
    }

    public function getLinkLocationPrefix(): string
    {
        if ($this->getOption('force_iplayer_linking')) {
            return 'map_iplayer_';
        }
        return $this->getOption('link_location_prefix');
    }

    public function getMainTitle(): string
    {
        if (!isset($this->mainTitleProgramme)) {
            $this->setTitleProgrammes();
        }

        return $this->mainTitleProgramme->getTitle();
    }

    public function getSubTitle(): string
    {
        if (!isset($this->subTitlesProgrammes)) {
            $this->setTitleProgrammes();
        }

        return implode(', ', array_map(function (CoreEntity $programme) {
            return $programme->getTitle();
        }, $this->subTitlesProgrammes));
    }

    public function getUrl(): string
    {
        $isEpisode = $this->programme instanceof Episode;

        // video episodes play in iPlayer
        if ($this->getOption('force_iplayer_linking') || ($isEpisode && $this->programme->isTv())) {
            return $this->router->generate(
                'iplayer_play',
                ['pid' => $this->programme->getPid()],
                UrlGenerator::ABSOLUTE_URL
            );
        }

        return $this->router->generate(
            'find_by_pid',
            [
                'pid' => $this->programme->getPid(),
                '_fragment' => $isEpisode ? 'live' : '',
            ],
            UrlGenerator::ABSOLUTE_URL
        );
    }

    protected function validateOptions(array $options): void
    {
        if (isset($options['context_programme']) && !($options['context_programme'] instanceof Programme)) {
            throw new InvalidOptionException('context_programme option must be null or a Programme domain object');
        }

        if (!is_bool($options['text_colour_on_title_link'])) {
            throw new InvalidOptionException('text_colour_on_title_link option must be a boolean');
        }
    }

    private function setTitleProgrammes(): void
    {
        [$this->mainTitleProgramme, $this->subTitlesProgrammes] = $this->titleHelper->getOrderedProgrammesForTitle(
            $this->programme,
            $this->options['context_programme'],
            $this->options['title_format']
        );
    }
}
