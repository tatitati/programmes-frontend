<?php
declare(strict_types = 1);
namespace App\Ds2013;

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
        \BBC\ProgrammesPagesService\Domain\Entity\Programme $programme,
        array $options = []
    ): Organism\Programme\ProgrammePresenter {
        return new Organism\Programme\ProgrammePresenter(
            $this,
            $programme,
            $options
        );
    }
}
