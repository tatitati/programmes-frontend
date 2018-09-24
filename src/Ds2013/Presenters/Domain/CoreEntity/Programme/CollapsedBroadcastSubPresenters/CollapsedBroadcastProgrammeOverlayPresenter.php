<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Domain\CoreEntity\Programme\CollapsedBroadcastSubPresenters;

use App\Ds2013\Presenters\Domain\CoreEntity\Programme\SubPresenters\ProgrammeOverlayPresenter;
use App\Ds2013\Presenters\Utilities\Cta\LiveCtaPresenter;
use App\DsShared\Helpers\LiveBroadcastHelper;
use App\DsShared\Helpers\PlayTranslationsHelper;
use App\DsShared\Helpers\StreamableHelper;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CollapsedBroadcastProgrammeOverlayPresenter extends ProgrammeOverlayPresenter
{
    /** @var CollapsedBroadcast */
    private $collapsedBroadcast;

    /** @var LiveBroadcastHelper */
    private $liveBroadcastHelper;

    /** @var PlayTranslationsHelper */
    private $playTranslationsHelper;

    /** @var StreamableHelper */
    private $streamUrlHelper;

    public function __construct(
        UrlGeneratorInterface $router,
        PlayTranslationsHelper $playTranslationsHelper,
        LiveBroadcastHelper $liveBroadcastHelper,
        StreamableHelper $streamUrlHelper,
        CollapsedBroadcast $broadcast,
        Programme $programme,
        array $options = []
    ) {
        parent::__construct($router, $programme, $options);
        $this->collapsedBroadcast = $broadcast;
        $this->liveBroadcastHelper = $liveBroadcastHelper;
        $this->playTranslationsHelper = $playTranslationsHelper;
        $this->streamUrlHelper = $streamUrlHelper;
    }

    public function getCollapsedBroadcast()
    {
        return $this->collapsedBroadcast;
    }

    public function getLiveCtaPresenter(): LiveCtaPresenter
    {
        return new LiveCtaPresenter(
            $this->collapsedBroadcast,
            $this->getOption('context_service'),
            $this->playTranslationsHelper,
            $this->router,
            $this->streamUrlHelper,
            $this->liveBroadcastHelper,
            []
        );
    }

    public function isWatchableLive(): bool
    {
        return $this->liveBroadcastHelper->isWatchableLive($this->collapsedBroadcast, $this->options['advanced_live']);
    }

    public function getTemplateVariableName(): string
    {
        return 'programme_overlay';
    }
}
