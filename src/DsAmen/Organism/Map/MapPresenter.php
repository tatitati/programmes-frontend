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
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\Promotion;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Media Availability Panel Presenter
 * Default layout is 3 columns and no minimapping. Left column takes up 1/2 the page, the others the other half
 * See docs/MAP.md for more info
 */
class MapPresenter extends Presenter
{
    /** @var HelperFactory */
    private $helperFactory;

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

    /** @var TranslateProvider */
    private $translateProvider;

    /** @var CollapsedBroadcast|null */
    private $upcomingBroadcast;

    /** @var CollapsedBroadcast|null */
    private $lastOn;

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var Episode|null*/
    private $streamableEpisode;

    /** @var Episode|null */
    private $upcomingEpisode;

    public function __construct(
        Request $request,
        HelperFactory $helperFactory,
        TranslateProvider $translateProvider,
        UrlGeneratorInterface $router,
        ProgrammeContainer $programme,
        ?CollapsedBroadcast $upcomingBroadcast,
        ?CollapsedBroadcast $lastOn,
        ?Promotion $comingSoonPromo,
        ?Episode $streamableEpisode,
        ?Episode $upcomingEpisode,
        int $debutsCount,
        int $repeatsCount,
        array $options = []
    ) {
        parent::__construct($options);
        $this->request = $request;
        $this->helperFactory = $helperFactory;
        $this->translateProvider = $translateProvider;
        $this->router = $router;
        $this->programme = $programme;
        $this->upcomingBroadcast = $upcomingBroadcast;
        $this->lastOn = $lastOn;
        $this->streamableEpisode = $streamableEpisode;
        $this->upcomingEpisode = $upcomingEpisode;

        $this->setShowMiniMap();

        $hasComingSoon = $comingSoonPromo || $this->programme->getOption('comingsoon_textonly');
        $this->showMap = $programme->getAggregatedEpisodesCount() || $hasComingSoon;
        if (!$this->showMap) {
            return;
        }

        if ($this->showThirdColumn($hasComingSoon)) {
            $this->constructThreeColumnMap($comingSoonPromo, $hasComingSoon, $debutsCount, $repeatsCount);
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

    private function constructThreeColumnMap(
        ?Promotion $comingSoonPromo,
        bool $hasComingSoon,
        int $debutsCount,
        int $repeatsCount
    ) {
        if ($this->showMiniMap) {
            $this->leftGridClasses = '1/3@gel3b';
            $this->rightGridClasses = '2/3@gel3b';
        }

        // Add columns to map
        if ($this->isWorldNews()) {
            $this->rightColumns[] = new LastOnPresenter($this->programme);
        } else {
            $this->rightColumns[] = new OnDemandPresenter(
                $this->programme,
                $this->streamableEpisode,
                $this->upcomingEpisode,
                $this->lastOn,
                ['full_width' => false, 'show_mini_map' => $this->showMiniMap]
            );
        }

        if ($hasComingSoon && !$this->upcomingBroadcast) {
            $this->rightColumns[] = new ComingSoonPresenter(
                $this->programme,
                $comingSoonPromo,
                [
                    'show_mini_map' => $this->showMiniMap,
                ]
            );
        } else {
            $this->rightColumns[] = new TxPresenter(
                $this->helperFactory->getLiveBroadcastHelper(),
                $this->translateProvider,
                $this->router,
                $this->programme,
                $this->upcomingBroadcast,
                $debutsCount,
                $repeatsCount,
                ['show_mini_map' => $this->showMiniMap]
            );
        }
    }

    private function constructTwoColumnMap()
    {
        $this->leftGridClasses = '2/3@gel3b';
        $this->rightGridClasses = '1/3@gel3b';

        $this->rightColumns[] = new OnDemandPresenter(
            $this->programme,
            $this->streamableEpisode,
            $this->upcomingEpisode,
            null,
            [
                'full_width' => true,
                'show_mini_map' => $this->showMiniMap,
            ]
        );
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

    private function showThirdColumn(bool $hasComingSoon)
    {
        $hasBroadcastInLast18Months = $this->lastOn ? $this->lastOn->getStartAt()->wasWithinLast('18 months') : false;

        return $this->upcomingBroadcast || $hasBroadcastInLast18Months || $hasComingSoon || $this->isWorldNews();
    }

    private function setShowMiniMap(): void
    {
        if ($this->request->query->has('__2016minimap')) {
            $this->showMiniMap = (bool) $this->request->query->get('__2016minimap');
            return;
        }
        // if ($this->is2016BrandPage() && $this->isVotePriority() ) return true;

        $this->showMiniMap = filter_var($this->programme->getOption('brand_2016_layout_use_minimap'), FILTER_VALIDATE_BOOLEAN);
    }
}
