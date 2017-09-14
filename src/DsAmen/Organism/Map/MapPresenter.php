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
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\Promotion;
use Symfony\Component\HttpFoundation\Request;

/**
 * Media Availability Panel Presenter
 * Default layout is 3 columns and no minimapping. Left column takes up 1/2 the page, the others the other half
 * See docs/MAP.md for more info
 */
class MapPresenter extends Presenter
{
    /** @var string */
    private $leftGridClasses = '1/2@gel3b';

    /** @var Programme */
    private $programme;

    /** @var Request */
    private $request;

    /** @var Presenter[] */
    private $rightColumns = [];

    /** @var string */
    private $rightGridClasses = '1/2@gel3b';

    /** @var bool */
    private $showMap;

    /** @var bool */
    private $showMiniMap = false;

    public function __construct(
        Request $request,
        ProgrammeContainer $programme,
        int $upcomingEpisodesCount,
        ?CollapsedBroadcast $mostRecentBroadcast,
        ?Promotion $promotion,
        array $options = []
    ) {
        parent::__construct($options);
        $this->programme = $programme;
        $this->request = $request;
        $hasComingSoon = $promotion || $this->programme->getOption('comingsoon_textonly');
        $programmeHasEpisodes = $programme->getAggregatedEpisodesCount() > 0;
        $this->showMap = $programmeHasEpisodes || $hasComingSoon;
        if (!$this->showMap) {
            return;
        }

        if ($this->showThirdColumn($hasComingSoon, $upcomingEpisodesCount, $mostRecentBroadcast)) {
            $this->constructThreeColumnMap($promotion, $hasComingSoon, $programmeHasEpisodes);
        } else {
            $this->constructTwoColumnMap();
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
            $this->programme,
            [
                'is_three_column' => $this->countTotalColumns() === 3,
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

    private function constructThreeColumnMap(?Promotion $promotion, bool $hasComingSoon, bool $programmeHasEpisodes)
    {
        $comingSoonTakeover = !$this->programme->getParent() && ($hasComingSoon && !$programmeHasEpisodes);

        if ($this->shouldShowMiniMap()) {
            $this->showMiniMap = true;
            $this->leftGridClasses = '1/3@gel3b';
            $this->rightGridClasses = '2/3@gel3b';
        }

        // Add columns to map
        if ($this->isWorldNews()) {
            $this->rightColumns[] = new LastOnPresenter($this->programme);
        } else {
            $this->rightColumns[] = new OnDemandPresenter($this->programme, ['full_width' => false]);
        }

        if ($hasComingSoon) {
            $this->rightColumns[] = new ComingSoonPresenter($this->programme, $promotion, ['show_mini_map' => $this->showMiniMap, 'show_synopsis' => $comingSoonTakeover]);
        } else {
            $this->rightColumns[] = new TxPresenter($this->programme);
        }
    }

    private function constructTwoColumnMap()
    {
        $this->leftGridClasses = '2/3@gel3b';
        $this->rightGridClasses = '1/3@gel3b';

        $this->rightColumns[] = new OnDemandPresenter($this->programme, ['full_width' => true]);
    }

    /**
     * This is the left column and the main right columns
     * The social column is ignored
     *
     * @return int
     */
    private function countTotalColumns()
    {
        return 1 + count($this->rightColumns);
    }

    private function isWorldNews(): bool
    {
        $network = $this->programme->getNetwork();
        if (is_null($network)) {
            return false;
        }
        return ((string) $network->getNid()) === 'bbc_world_news';
    }

    private function showThirdColumn(bool $hasComingSoon, int $upcomingEpisodesCount, ?CollapsedBroadcast $mostRecentBroadcast)
    {
        $existUpcomingBroadcasts = $upcomingEpisodesCount > 0;

        $hasBroadcastInLast18Months = $mostRecentBroadcast ? $mostRecentBroadcast->getStartAt()->wasWithinLast('18 months') : false;

        return $existUpcomingBroadcasts || $hasBroadcastInLast18Months || $hasComingSoon || $this->isWorldNews();
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
