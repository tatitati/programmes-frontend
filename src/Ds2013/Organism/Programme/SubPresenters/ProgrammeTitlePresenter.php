<?php
declare(strict_types = 1);
namespace App\Ds2013\Organism\Programme\SubPresenters;

use App\Ds2013\Helpers\TitleLogicHelper;
use App\Ds2013\Organism\Programme\ProgrammePresenterBase;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Sub-presenter for ProgrammePresenter
 */
class ProgrammeTitlePresenter extends ProgrammePresenterBase
{
    /** @var Programme */
    private $mainTitleProgramme;

    /** @var Programme[] */
    private $subTitlesProgrammes;

    /** @var array */
    protected $options = [
        'title_format' => 'tleo::ancestry:item',
        'title_tag' => 'h4',
        'title_classes' => '',
        'show_subtitle' => true,
    ];

    /** @var TitleLogicHelper */
    private $titleHelper;

    public function __construct(
        UrlGeneratorInterface $router,
        TitleLogicHelper $titleHelper,
        Programme $programme,
        array $options = []
    ) {
        parent::__construct($router, $programme, $options);
        $this->titleHelper = $titleHelper;

        if ($this->programme instanceof Clip && $this->programme->isAudio()) {
            // don't show the subtitle for Audio clips
            $this->options['show_subtitle'] = false;
        }
    }

    public function getTitleLinkUrl(): string
    {
        // Link to iplayer/podcasts will be added here later
        return $this->router->generate('find_by_pid', ['pid' => $this->programme->getPid()], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function getMainTitleProgramme(): Programme
    {
        if (!isset($this->mainTitleProgramme)) {
            $this->setTitleProgrammes();
        }
        return $this->mainTitleProgramme;
    }

    /**
     * @return Programme[]
     */
    public function getSubTitlesProgrammes(): array
    {
        if (!isset($this->subTitlesProgrammes)) {
            $this->setTitleProgrammes();
        }
        return $this->subTitlesProgrammes;
    }

    private function setTitleProgrammes(): void
    {
        list($this->mainTitleProgramme, $this->subTitlesProgrammes) = $this->titleHelper->getOrderedProgrammesForTitle(
            $this->programme,
            $this->options['context_programme'],
            $this->options['title_format']
        );
    }
}
