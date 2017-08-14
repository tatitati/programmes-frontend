<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Map;

use App\DsAmen\Organism\Map\SubPresenter\ComingSoonPresenter;
use App\DsAmen\Organism\Map\SubPresenter\LastOnPresenter;
use App\DsAmen\Organism\Map\SubPresenter\OnDemandPresenter;
use App\DsAmen\Organism\Map\SubPresenter\ProgrammeInfoPresenter;
use App\DsAmen\Organism\Map\SubPresenter\PromoPresenter;
use App\DsAmen\Organism\Map\SubPresenter\SocialPresenter;
use App\DsAmen\Organism\Map\SubPresenter\TxPresenter;
use App\DsAmen\Presenter;
use App\DsShared\Helpers\HelperFactory;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use Symfony\Component\HttpFoundation\Request;

/**
 * Media Availability Panel Presenter
 */
class MapPresenter extends Presenter
{
    /** @var bool */
    private $comingSoonTakeover;

    /** @var bool */
    public $isActive = false;

    /** @var HelperFactory */
    private $helperFactory;

    /** @var string */
    private $leftGridClasses = '1/2@gel3b';

    /** @var bool */
    private $mustShowTxColumn = false;

    /** @var Programme */
    private $programme;

    /** @var Presenter[] */
    private $rightColumns = [];

    /** @var string */
    private $rightGridClasses = '1/2@gel3b';

    /** @var bool */
    private $showMiniMap = false;

    /** @var bool */
    private $showMap;

    /** @var Request */
    private $request;

    public function __construct(
        Request $request,
        HelperFactory $helperFactory,
        ProgrammeContainer $programme,
        int $upcomingEpisodesCount,
        ?CollapsedBroadcast $mostRecentBroadcast,
        array $options = []
    ) {
        parent::__construct($options);
        $this->programme = $programme;
        $this->request = $request;
        $this->helperFactory = $helperFactory;
        $hasComingSoon = $this->programme->getOption('coming_soon') || $this->programme->getOption('comingsoon_textonly');
        $programmeHasEpisodes = $programme->getAggregatedEpisodesCount() > 0;
        $this->showMap = $programmeHasEpisodes || $hasComingSoon;
        if (!$this->showMap) {
            return;
        }

        $this->comingSoonTakeover = !$this->programme->getParent() && ($hasComingSoon && !$programmeHasEpisodes);

        if ($this->shouldShowMiniMap()) {
            $this->showMiniMap = true;
        }

        // Add columns to map
        if ($this->comingSoonTakeover) {
            $this->rightColumns[] = new OnDemandPresenter($programme, ['must_show_tx_column' => false]); // I hope once the other columns are done, we won't need to pass in must_show_tx_column
            $this->rightColumns[] = new ComingSoonPresenter($programme);
        } elseif ($this->isWorldNews()) {
            $this->rightColumns[] = new LastOnPresenter($programme);
            $this->rightColumns[] = new TxPresenter($programme, ['must_show_tx_column' => false]);
        } elseif ($this->mustShowTxColumn($hasComingSoon, $upcomingEpisodesCount, $mostRecentBroadcast)) {
            $this->mustShowTxColumn = true;

            // The other cases probably also need to take in to account the minimap option, but we will do that when we do each column.
            // Minimap has a different set of widths
            if ($this->showMiniMap) {
                $this->leftGridClasses = '1/3@gel3b';
                $this->rightGridClasses = '2/3@gel3b';
            }

            $this->rightColumns[] = new OnDemandPresenter($programme, ['must_show_tx_column' => true]);
            $this->rightColumns[] = new TxPresenter($programme, ['must_show_tx_column' => true]);
        } else {
            $this->leftGridClasses = '2/3@gel3b';
            $this->rightGridClasses = '1/3@gel3b';

            $this->rightColumns[] = new OnDemandPresenter($programme, ['must_show_tx_column' => false]);
        }
    }

    public function getLeftGridClasses(): string
    {
        return $this->leftGridClasses;
    }

    public function getProgramme(): Programme
    {
        return $this->programme;
    }

    public function getProgrammeInfoPresenter(): ProgrammeInfoPresenter
    {
        return new ProgrammeInfoPresenter(
            $this->helperFactory->getProseToParagraphsHelper(),
            $this->programme,
            [
                'is_three_column' => $this->comingSoonTakeover || $this->isWorldNews() || $this->mustShowTxColumn,
                'show_mini_map' => $this->showMiniMap,
            ]
        );
    }

    public function getPromo()
    {
        return false; //@TODO
    }

    public function getPromoPresenter(): PromoPresenter
    {
        return new PromoPresenter($this->programme);
    }

    /**
     * @return Presenter[]
     */
    public function getRightColumns(): array
    {
        return $this->rightColumns;
    }

    public function getRightGridClasses(): string
    {
        return $this->rightGridClasses;
    }

    public function getSocialPresenter(): SocialPresenter
    {
        return new SocialPresenter($this->programme);
    }

    public function showMap(): bool
    {
        return $this->showMap;
    }

    private function isWorldNews(): bool
    {
        $network = $this->programme->getNetwork();
        if (is_null($network)) {
            return false;
        }
        return ((string) $network->getNid()) === 'bbc_world_news';
    }

    private function mustShowTxColumn(bool $hasComingSoon, int $upcomingEpisodesCount, ?CollapsedBroadcast $mostRecentBroadcast)
    {
        $existUpcomingBroadcasts = $upcomingEpisodesCount > 0;

        $hasBroadcastInLast18Months = $mostRecentBroadcast ? $mostRecentBroadcast->getStartAt()->wasWithinLast('18 months') : false;

        return $existUpcomingBroadcasts || $hasBroadcastInLast18Months || $hasComingSoon;
    }

    private function shouldShowMiniMap(): bool
    {
        if ($this->request->query->has('__2016minimap')) {
            return (bool) $this->request->query->get('__2016minimap');
        }
        // if ($this->is2016BrandPage() && $this->isVotePriority() ) return true;

        return filter_var($this->programme->getOption('brand_2016_layout_use_minimap'), FILTER_VALIDATE_BOOLEAN);
    }
}
