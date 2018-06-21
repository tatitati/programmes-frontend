<?php
namespace App\Ds2013\Presenters\Section\Clip\Details;

use App\Ds2013\Presenter;
use App\DsShared\Helpers\PlayTranslationsHelper;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use DateTime;

class ClipDetailsPresenter extends Presenter
{
    /** @var Clip */
    private $clip;

    /** @var PlayTranslationsHelper */
    private $playTranslationsHelper;

    /** @var Contribution[] */
    private $contributions;

    public function __construct(Clip $clip, array $contributions, PlayTranslationsHelper $playTranslationsHelper, array $options = [])
    {
        $this->clip = $clip;
        $this->contributions = $contributions;
        $this->playTranslationsHelper = $playTranslationsHelper;

        parent::__construct($options);
    }

    public function getClip(): Clip
    {
        return $this->clip;
    }

    /**
     * @return Contribution[]
     */
    public function getContributions(): array
    {
        return $this->contributions;
    }

    public function getReleaseDate(): ?DateTime
    {
        if ($this->clip->getReleaseDate()) {
            return $this->clip->getReleaseDate()->asDateTime();
        }

        return null;
    }


    public function isAvailableIndefinitely(): bool
    {
        if (!$this->clip->getStreamableUntil()) {
            return true;
        }

        return !$this->clip->getStreamableUntil()->isWithinNext('1 year');
    }

    public function getStreamableTimeRemaining(): string
    {
        return $this->playTranslationsHelper->translateAvailableUntilToWords($this->clip);
    }

    public function getWordyDuration(): ?string
    {
        if ($this->clip->getDuration()) {
            return $this->playTranslationsHelper->secondsToWords($this->clip->getDuration());
        }

        return null;
    }
}
