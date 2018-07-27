<?php
declare(strict_types=1);
namespace App\Ds2013\Presenters\Section\Episode\Map\Panels\Main;

use App\Ds2013\Presenter;
use App\DsShared\Helpers\PlayTranslationsHelper;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Podcast;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use Cake\Chronos\Chronos;
use DateTime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DetailsPresenter extends Presenter
{
    /** @var Episode */
    private $episode;

    /** @var PlayTranslationsHelper */
    private $playTranslationsHelper;

    /** @var Version[] */
    private $availableVersions;

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var Podcast|null */
    private $podcast;

    public function __construct(PlayTranslationsHelper $playTranslationsHelper, UrlGeneratorInterface $router, Episode $episode, array $availableVersions, ?Podcast $podcast)
    {
        parent::__construct();

        $this->episode = $episode;
        $this->playTranslationsHelper = $playTranslationsHelper;
        $this->router = $router;
        $this->availableVersions = $availableVersions;
        $this->podcast = $podcast;
    }

    public function getEpisode(): Episode
    {
        return $this->episode;
    }

    public function getPodcast(): ?Podcast
    {
        return $this->podcast;
    }

    public function canBeDownloaded(): bool
    {
        return $this->getDownloadableVersion() && $this->episode->isDownloadable();
    }

    public function getDownloadableVersion(): ?Version
    {
        foreach ($this->availableVersions as $version) {
            if ($version->isDownloadable()) {
                return $version;
            }
        }

        return null;
    }

    public function hasPreviousBroadcast(): bool
    {
        return $this->episode->getFirstBroadcastDate() && $this->episode->getFirstBroadcastDate()->isPast();
    }

    public function getReleaseDate(): ?DateTime
    {
        if ($this->episode->getReleaseDate()) {
            return $this->episode->getReleaseDate()->asDateTime();
        }

        return null;
    }

    /**
     * @see https://confluence.dev.bbc.co.uk/display/programmes/Versions+and+Availability
     */
    public function isAvailableIndefinitely(): bool
    {
        if (!$this->episode->getStreamableUntil()) {
            return true;
        }

        return !$this->episode->getStreamableUntil()->isWithinNext('1 year');
    }

    public function getStreamableTimeRemaining(): string
    {
        return $this->playTranslationsHelper->translateAvailableUntilToWords($this->episode, null, false);
    }

    public function getDuration(): string
    {
        return $this->playTranslationsHelper->secondsToWords($this->episode->getDuration());
    }

    public function hasAvailableAudioDescribedVersion(): bool
    {
        return $this->hasAvailableVersion('DubbedAudioDescribed');
    }

    public function hasAvailableSignedVersion(): bool
    {
        return $this->hasAvailableVersion('Signed');
    }

    private function hasAvailableVersion(string $versionType)
    {
        foreach ($this->availableVersions as $version) {
            if ($version->isStreamable()) {
                foreach ($version->getVersionTypes() as $type) {
                    if ($type->getType() === $versionType) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
