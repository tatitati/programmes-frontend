<?php
declare(strict_types=1);

namespace App\Ds2013\Presenters\Section\Episode\Map;

use App\Ds2013\Presenter;
use App\Ds2013\Presenters\Section\Episode\Map\Panels\Main\PanelDetailsPresenter;
use App\Ds2013\Presenters\Section\Episode\Map\Panels\Main\PanelPlayoutPresenter;
use App\Ds2013\Presenters\Section\Episode\Map\Panels\Side\PanelEmptyPresenter;
use App\Ds2013\Presenters\Section\Episode\Map\Panels\Side\PanelMorePresenter;
use App\Ds2013\Presenters\Section\Episode\Map\Panels\Side\PanelTxPresenter;
use App\DsShared\Helpers\PlayTranslationsHelper;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;

class EpisodeMapPresenter extends Presenter
{
    /** @var CollapsedBroadcast|null */
    private $upcomingBroadcast;

    /** @var CollapsedBroadcast|null */
    private $lastOnBroadcast;

    /** @var Episode */
    private $episode;

    /**
     * @var Presenter[]
     */
    private $sideSubPresenters;

    /** @var PanelPlayoutPresenter */
    private $playoutSubpresenter;

    /** @var PanelDetailsPresenter */
    private $detailsSubpresenter;

    public function __construct(PlayTranslationsHelper $playTranslationsHelper, Episode $episode, array $streamableVersions, ?CollapsedBroadcast $upcomingBroadcasts, ?CollapsedBroadcast $lastOnBroadcasts)
    {
        parent::__construct();
        $this->episode            = $episode;
        $this->upcomingBroadcast = $upcomingBroadcasts;
        $this->lastOnBroadcast   = $lastOnBroadcasts;
        $this->sideSubPresenters  = $this->buildSidePanelsSubPresenters();
        $this->playoutSubpresenter  = new PanelPlayoutPresenter($episode);
        $this->detailsSubpresenter  = new PanelDetailsPresenter($playTranslationsHelper, $episode, $streamableVersions);
    }

    /**
     * @return array of Presenter
     */
    public function getSidePanelsSubPresenters() :array
    {
        return $this->sideSubPresenters;
    }

    public function getPlayoutSubpresenter() :PanelPlayoutPresenter
    {
        return $this->playoutSubpresenter;
    }

    public function getDetailsSubpresenter() :PanelDetailsPresenter
    {
        return $this->detailsSubpresenter;
    }

    /**
     * Show/hide panels
     */
    private function mustDisplayTxPanel() :bool
    {
        return !is_null($this->upcomingBroadcast) || !is_null($this->lastOnBroadcast);
    }

    private function buildSidePanelsSubPresenters() :array
    {
        $sidePanels = [];
        if ($this->mustDisplayTxPanel()) {
            $sidePanels[] = new PanelTxPresenter($this->episode, $this->upcomingBroadcast, $this->lastOnBroadcast);
        }

        if (!$this->episode->isTleo()) {
            $sidePanels[] = new PanelMorePresenter($this->episode);
        }

        if (empty($sidePanels) || $this->episode->isTleo()) {
            $sidePanels[] = new PanelEmptyPresenter();
        }

        return $sidePanels;
    }
}
