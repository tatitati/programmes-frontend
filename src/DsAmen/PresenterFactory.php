<?php
declare(strict_types = 1);

namespace App\DsAmen;

use App\DsAmen\Molecule\Duration\DurationPresenter;
use App\DsAmen\Molecule\Synopsis\SynopsisPresenter;
use App\DsAmen\Organism\Map\MapPresenter;
use App\DsAmen\Organism\Programme\CollapsedBroadcastPresenter;
use App\DsAmen\Organism\Programme\ProgrammePresenter;
use App\DsAmen\Organism\Promotion\PromotionPresenter;
use App\DsShared\Helpers\HelperFactory;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\Promotion;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * DsAmen Factory Class for creating presenters.
 */
class PresenterFactory
{
    /** @var TranslateProvider */
    private $translateProvider;

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var HelperFactory */
    private $helperFactory;

    public function __construct(TranslateProvider $translateProvider, UrlGeneratorInterface $router, HelperFactory $helperFactory)
    {
        $this->translateProvider = $translateProvider;
        $this->router = $router;
        $this->helperFactory = $helperFactory;
    }

    public function durationPresenter(int $duration, array $options = []): DurationPresenter
    {
        return new DurationPresenter($duration, $this->translateProvider, $options);
    }

    public function mapPresenter(Request $request, ProgrammeContainer $programme, int $upcomingEpisodesCount, ?CollapsedBroadcast $mostRecentBroadcast, ?Promotion $promotion): MapPresenter
    {
        return new MapPresenter($request, $programme, $upcomingEpisodesCount, $mostRecentBroadcast, $promotion);
    }

    public function collapsedBroadcastPresenter(CollapsedBroadcast $collapsedBroadcast, array $options = []): CollapsedBroadcastPresenter
    {
        return new CollapsedBroadcastPresenter($collapsedBroadcast, $this->router, $this->translateProvider, $this->helperFactory, $options);
    }

    public function programmePresenter(Programme $programme, array $options = []): ProgrammePresenter
    {
        return new ProgrammePresenter($this->router, $this->helperFactory, $programme, $options);
    }

    public function promotionPresenter(Promotion $promotion, array $options = []): PromotionPresenter
    {
        return new PromotionPresenter($this->router, $promotion, $options);
    }

    public function synopsisPresenter(Synopses $synopses, int $maxLength): SynopsisPresenter
    {
        return new SynopsisPresenter($synopses, $maxLength);
    }
}
