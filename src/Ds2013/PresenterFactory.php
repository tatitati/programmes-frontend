<?php
declare(strict_types = 1);
namespace App\Ds2013;

use App\Ds2013\Organism\Broadcast\BroadcastPresenter;
use App\Ds2013\Organism\Programme\ProgrammePresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;

/**
 * Ds2013 Factory Class for creating presenters.
 *
 * This abstraction shall allow us to have a single entry point to create any
 * Presenter. This is particularly valuable in two cases:
 * 1) When a presenter depends upon another presenter - we can pass in this
 *    factory to all presenters to it is trivial to create an new one
 * 2) When we have multiple Domain objects that should all be rendered using the
 *    same template. This factory allows us to choose the correct presenter for
 *    a given domain object.
 *
 * This class has create methods for all molecules, organisms and templates
 * which have presenters.
 * Each respective group MUST have the methods kept in alphabetical order
 *
 * To instantiate Amen you MUST pass it a translation locale (e.g en_GB)
 * All presenters MUST be created using this factory.
 * All presenters MUST call the base Presenter __construct method
 *
 */
class PresenterFactory
{
    /** @var string */
    private $locale;

    public function __construct(string $locale)
    {
        $this->locale = $locale;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * Create a programme presenter class
     */
    public function programmePresenter(
        Programme $programme,
        array $options = []
    ): ProgrammePresenter {
        return new ProgrammePresenter(
            $this,
            $programme,
            $options
        );
    }

    public function broadcastPresenter(
        Broadcast $broadcast,
        array $options = []
    ): BroadcastPresenter {
        return new BroadcastPresenter(
            $this,
            $broadcast,
            $options
        );
    }
}
