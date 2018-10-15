<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Section\Clip\Playout;

use App\Ds2013\Presenter;
use App\Ds2013\PresenterFactory;
use App\Ds2013\Presenters\Utilities\SMP\SmpPresenter;
use App\DsShared\Helpers\StreamableHelper;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Version;

class ClipPlayoutPresenter extends Presenter
{
    /** @var Clip */
    private $clip;

    /** @var Version */
    private $streamableVersion;

    /** @var array */
    private $segmentEvents;

    /** @var string */
    private $analyticsCounterName;

    /** @var array */
    private $istatsAnalyticsLabels;

    /** @var PresenterFactory */
    private $presenterFactory;

    /** @var StreamableHelper */
    private $streamableHelper;

    public function __construct(
        PresenterFactory $presenterFactory,
        StreamableHelper $streamableHelper,
        Clip $clip,
        ?Version $streamableVersion,
        array $segmentEvents,
        string $analyticsCounterName,
        array $istatsAnalyticsLabels,
        array $options = []
    ) {
        parent::__construct($options);
        $this->clip = $clip;
        $this->streamableVersion = $streamableVersion;
        $this->segmentEvents = $segmentEvents;
        $this->analyticsCounterName = $analyticsCounterName;
        $this->istatsAnalyticsLabels = $istatsAnalyticsLabels;
        $this->presenterFactory = $presenterFactory;
        $this->streamableHelper = $streamableHelper;
    }

    public function getClip(): Clip
    {
        return $this->clip;
    }

    public function shouldStreamViaPlayspace(): bool
    {
        return $this->streamableHelper->shouldStreamViaPlayspace($this->clip);
    }

    public function getSmpPresenter(): SmpPresenter
    {
        return $this->presenterFactory->smpPresenter($this->clip, $this->streamableVersion, $this->segmentEvents, $this->analyticsCounterName, $this->istatsAnalyticsLabels, []);
    }
}
