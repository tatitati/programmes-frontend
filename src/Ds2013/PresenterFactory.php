<?php
declare(strict_types = 1);
namespace App\Ds2013;

use App\Ds2013\Helpers\HelperFactory;
use App\Ds2013\Molecule\Calendar\CalendarPresenter;
use App\Ds2013\Molecule\DateList\DateListPresenter;
use App\Ds2013\Molecule\Image\ImagePresenter;
use App\Ds2013\Organism\Broadcast\BroadcastPresenter;
use App\Ds2013\Organism\Programme\BroadcastProgrammePresenter;
use App\Ds2013\Organism\Programme\CollapsedBroadcastProgrammePresenter;
use App\Ds2013\Organism\Programme\ProgrammePresenter;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\ChronosInterface;
use InvalidArgumentException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
 * To instantiate Ds2013 you MUST pass it an instance of TranslateProvider
 * All presenters MUST be created using this factory.
 * All presenters MUST call the base Presenter __construct method
 *
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

    /**
     * Molecules
     */
    public function calendarPresenter(
        ChronosInterface $datetime,
        Service $service,
        array $options = []
    ): CalendarPresenter {
        return new CalendarPresenter(
            $datetime,
            $service,
            $options
        );
    }

    public function dateListPresenter(
        ChronosInterface $datetime,
        Service $service,
        array $options = []
    ): DateListPresenter {
        return new DateListPresenter(
            $this->router,
            $datetime,
            $service,
            $options
        );
    }

    public function imagePresenter(
        Image $image,
        int $defaultWidth,
        $sizes,
        array $options = []
    ): ImagePresenter {
        return new ImagePresenter(
            $image,
            $defaultWidth,
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
            $this->router,
            $this->helperFactory,
            $programme,
            $options
        );
    }

    /**
     * A Broadcast Programme is a special case of the Programme Presenter, that
     * contains additional information about a given broadcast of a programme.
     *
     * Usually this shall be a CollapasedBroadcast, however sometimes you may
     * only have a Broadcast to hand.
     *
     * You may pass in an explicit programme in as an argument in case the
     * programme attached to $broadcast does not have a full hierarchy attached
     * to it.
     *
     * @param Broadcast|CollapsedBroadcast $broadcast
     * @param Programme|null $programme
     */
    public function broadcastProgrammePresenter(
        $broadcast,
        ?Programme $programme = null,
        array $options = []
    ) {
        if ($broadcast instanceof CollapsedBroadcast) {
            return new CollapsedBroadcastProgrammePresenter(
                $this->router,
                $this->helperFactory,
                $broadcast,
                $programme ?? $broadcast->getProgrammeItem(),
                $options
            );
        }

        if ($broadcast instanceof Broadcast) {
            return new BroadcastProgrammePresenter(
                $this->router,
                $this->helperFactory,
                $broadcast,
                $programme ?? $broadcast->getProgrammeItem(),
                $options
            );
        }

        throw new InvalidArgumentException(sprintf(
            'Expected $broadcast to be an instance of "%s" or "%s". Found instance of "%s"',
            Broadcast::CLASS,
            CollapsedBroadcast::CLASS,
            (is_object($broadcast) ? get_class($broadcast) : gettype($broadcast))
        ));
    }

    public function broadcastPresenter(
        $broadcast,
        ?CollapsedBroadcast $collapsedBroadcast,
        array $options = []
    ): BroadcastPresenter {
        return new BroadcastPresenter(
            $broadcast,
            $collapsedBroadcast,
            $options
        );
    }
}
