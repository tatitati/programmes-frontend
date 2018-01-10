<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Domain\Programme\BroadcastSubPresenters;

use App\Ds2013\Presenters\Domain\Programme\SubPresenters\ProgrammeTitlePresenter;
use App\DsShared\Helpers\TitleLogicHelper;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\BroadcastInfoInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Sub-presenter for ProgrammePresenter
 */
class BroadcastProgrammeTitlePresenter extends ProgrammeTitlePresenter
{
    /** @var BroadcastInfoInterface */
    private $broadcast;

    public function __construct(
        UrlGeneratorInterface $router,
        TitleLogicHelper $titleHelper,
        BroadcastInfoInterface $broadcast,
        Programme $programme,
        array $options = []
    ) {
        parent::__construct($router, $titleHelper, $programme, $options);
        $this->broadcast = $broadcast;
    }

    public function getAriaTitle(): string
    {
        $title = $this->broadcast->getStartAt()->setTimezone(ApplicationTime::getLocalTimeZone())->format('H:i') . ': ';
        $title .= $this->getMainTitleProgramme()->getTitle();
        if ($this->getOption('show_subtitle') && $this->getSubTitlesProgrammes()) {
            foreach ($this->getSubTitlesProgrammes() as $subTitle) {
                $title .= ', ' . $subTitle->getTitle();
            }
        }
        return $title;
    }

    public function getTemplateVariableName(): string
    {
        return 'programme_title';
    }
}
