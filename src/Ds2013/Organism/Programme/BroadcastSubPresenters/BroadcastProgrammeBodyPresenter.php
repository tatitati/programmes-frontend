<?php
declare(strict_types = 1);

namespace App\Ds2013\Organism\Programme\BroadcastSubPresenters;

use App\Ds2013\Helpers\PlayTranslationsHelper;
use App\Ds2013\Organism\Programme\SubPresenters\ProgrammeBodyPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BroadcastProgrammeBodyPresenter extends ProgrammeBodyPresenter
{
    /** @var Broadcast */
    private $broadcast;

    public function __construct(
        UrlGeneratorInterface $router,
        PlayTranslationsHelper $playTranslationsHelper,
        Broadcast $broadcast,
        Programme $programme,
        array $options = []
    ) {
        parent::__construct($router, $playTranslationsHelper, $programme, $options);
        $this->broadcast = $broadcast;
    }

    public function isRepeat(): bool
    {
        return $this->broadcast->isRepeat();
    }

    public function getTemplateVariableName(): string
    {
        return 'programme_body';
    }
}
