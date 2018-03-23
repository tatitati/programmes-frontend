<?php
declare(strict_types = 1);
namespace App\Ds2013\Presenters\Domain\CoreEntity\SharedSubPresenters;

use App\Ds2013\Presenter;
use App\DsShared\Helpers\TitleLogicHelper;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Sub-presenter for ProgrammePresenter
 */
class CoreEntityTitlePresenter extends Presenter
{
    /** @var CoreEntity */
    protected $coreEntity;

    /** @var UrlGeneratorInterface */
    protected $router;

    /** @var CoreEntity */
    protected $mainTitleProgramme;

    /** @var CoreEntity[] */
    protected $subTitlesProgrammes;

    /** @var array */
    protected $options = [
        'context_programme' => null,
        'title_format' => 'tleo::ancestry:item',
        'title_tag' => 'h4',
        'title_classes' => '',
        'show_subtitle' => true,
        'truncation_length' => null,
    ];

    /** @var TitleLogicHelper */
    private $titleHelper;

    public function __construct(
        UrlGeneratorInterface $router,
        TitleLogicHelper $titleHelper,
        CoreEntity $coreEntity,
        array $options = []
    ) {
        parent::__construct($options);
        $this->router = $router;
        $this->titleHelper = $titleHelper;
        $this->coreEntity = $coreEntity;

        if ($this->coreEntity instanceof Clip && $this->coreEntity->isAudio()) {
            // don't show the subtitle for Audio clips
            $this->options['show_subtitle'] = false;
        }
    }

    public function getTitleLinkUrl(): string
    {
        // Link to iplayer/podcasts will be added here later
        return $this->router->generate('find_by_pid', ['pid' => $this->coreEntity->getPid()], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function getMainTitleProgramme(): CoreEntity
    {
        if (!isset($this->mainTitleProgramme)) {
            $this->setTitleProgrammes();
        }
        return $this->mainTitleProgramme;
    }

    /**
     * @return CoreEntity[]
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
            $this->coreEntity,
            $this->options['context_programme'],
            $this->options['title_format']
        );
    }
}
