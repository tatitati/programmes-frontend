<?php
declare(strict_types = 1);
namespace App\Ds2013;

use App\Ds2013\Organism\Broadcast\BroadcastPresenter;
use App\Ds2013\Organism\Programme\ProgrammePresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
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
}
