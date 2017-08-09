<?php
declare(strict_types = 1);

namespace App\DsAmen;

use App\DsAmen\Molecule\ResponsiveImage\ResponsiveImagePresenter;
use App\DsAmen\Organism\Map\MapPresenter;
use App\DsAmen\Organism\Programme\ProgrammePresenter;
use App\DsShared\Helpers\HelperFactory;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
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

    public function mapPresenter(Request $request, Programme $programme, int $upcomingEpisodesCount, ?CollapsedBroadcast $mostRecentBroadcast): MapPresenter
    {
        return new MapPresenter($request, $programme, $upcomingEpisodesCount, $mostRecentBroadcast);
    }

    public function programmePresenter(Programme $programme, array $options = [])
    {
        return new ProgrammePresenter($programme, $options);
    }
}
