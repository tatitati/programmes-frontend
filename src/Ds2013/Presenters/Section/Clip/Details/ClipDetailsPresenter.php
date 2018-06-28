<?php
namespace App\Ds2013\Presenters\Section\Clip\Details;

use App\Ds2013\Presenter;
use App\DsShared\Helpers\PlayTranslationsHelper;
use App\ExternalApi\RmsPodcast\Domain\RmsPodcast;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use DateTime;

class ClipDetailsPresenter extends Presenter
{
    /** @var Clip */
    private $clip;

    /** @var PlayTranslationsHelper */
    private $playTranslationsHelper;

    /** @var Contribution[] */
    private $contributions;

    /** @var Version|null */
    private $version;

    /** @var RmsPodcast|null */
    private $rmsPodcast;

    public function __construct(PlayTranslationsHelper $playTranslationsHelper, Clip $clip, array $contributions, ?Version $version, ?RmsPodcast $rmsPodcast, array $options = [])
    {
        $this->clip = $clip;
        $this->version = $version;
        $this->rmsPodcast = $rmsPodcast;
        $this->contributions = $contributions;
        $this->playTranslationsHelper = $playTranslationsHelper;

        parent::__construct($options);
    }

    public function getClip(): Clip
    {
        return $this->clip;
    }

    public function getVersion(): ?Version
    {
        return $this->version;
    }

    public function getRmsPodcast(): ?RmsPodcast
    {
        return $this->rmsPodcast;
    }

    public function canBeDownloaded(): bool
    {
        return $this->version && $this->version->isDownloadable() && $this->clip->isDownloadable();
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
