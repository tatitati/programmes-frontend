<?php
declare(strict_types = 1);
namespace App\Ds2013;

use App\Ds2013\Molecule\DateList\DateListPresenter;
use App\Ds2013\Molecule\Image\ImagePresenter;
use App\Ds2013\Organism\Broadcast\BroadcastPresenter;
use App\Ds2013\Organism\Programme\ProgrammePresenter;
use App\Ds2013\Page\Schedules\ByDayPage\SchedulesByDayPagePresenter;
use App\Ds2013\Page\Schedules\NetworkServicesList\NetworkServicesListPresenter;
use App\Ds2013\Page\Schedules\RegionPick\RegionPickPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Chronos;
use DateTimeImmutable;
use RMP\Translate\Translate;

/**
 * Ds2013 Factory Class for creating presenters.
 *
 * This abstraction shall allow us to have a single entry point to create any
 * Presenter. This is particularly valuable in two cases:
 * 1) When presenters require Translate, we have a single point to inject it
 * 2) When we have multiple Domain objects that should all be rendered using the
 *    same template. This factory allows us to choose the correct presenter for
 *    a given domain object.
 *
 * This class has create methods for all molecules, organisms and templates
 * which have presenters.
 * Each respective group MUST have the methods kept in alphabetical order
 *
 * To instantiate Ds2013 you MUST pass it an instance of Translate
 * All presenters MUST be created using this factory.
 * All presenters MUST call the base Presenter __construct method
 *
 */
class PresenterFactory
{
    /** @var Translate */
    private $translate;

    public function __construct(Translate $translate)
    {
        $this->translate = $translate;
    }

    public function getTranslate(): Translate
    {
        return $this->translate;
    }

    public function setTranslate(Translate $translate): void
    {
        $this->translate = $translate;
    }

    /**
     * Molecules
     */
    public function dateListPresenter(
        Chronos $datetime,
        Service $service,
        array $options = []
    ): DateListPresenter {
        return new DateListPresenter(
            $datetime,
            $service,
            $options
        );
    }

    public function imagePresenter(
        Image $image,
        $sizes,
        array $options = []
    ): ImagePresenter {
        return new ImagePresenter(
            $image,
            $sizes,
            $options
        );
    }

    /**
     * Organisms
     */

    /**
     * Create a programme presenter class
     */
    public function programmePresenter(
        Programme $programme,
        array $options = []
    ): ProgrammePresenter {
        return new ProgrammePresenter(
            $this->translate,
            $programme,
            $options
        );
    }

    public function broadcastPresenter(
        Broadcast $broadcast,
        array $options = []
    ): BroadcastPresenter {
        return new BroadcastPresenter(
            $broadcast,
            $options
        );
    }

    /**
     * Page Presenters
     */
    public function schedulesByDayPagePresenter(
        Service $service,
        Chronos $startDateTime,
        Chronos $endDateTime,
        array $broadcasts,
        ?string $routeDate,
        array $servicesInNetwork,
        array $options = []
    ) {
        return new SchedulesByDayPagePresenter(
            $service,
            $startDateTime,
            $endDateTime,
            $broadcasts,
            $routeDate,
            $servicesInNetwork,
            $options
        );
    }
}
