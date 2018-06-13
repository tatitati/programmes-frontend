<?php
declare(strict_types=1);
namespace App\Ds2013\Presenters\Section\Episode\Map\Panels\Main;

use App\Ds2013\Presenter;
use App\DsShared\Helpers\PlayTranslationsHelper;
use App\ExternalApi\RmsPodcast\Domain\RmsPodcast;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Cake\Chronos\Chronos;
use DateTime;
use Exception;
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

    /** @var RmsPodcast|null */
    private $rmsPodcast;

    public function __construct(PlayTranslationsHelper $playTranslationsHelper, UrlGeneratorInterface $router, Episode $episode, array $availableVersions, ?RmsPodcast $rmsPodcast)
    {
        parent::__construct();

        $this->episode = $episode;
        $this->playTranslationsHelper = $playTranslationsHelper;
        $this->router = $router;
        $this->availableVersions = $availableVersions;
        $this->rmsPodcast = $rmsPodcast;
    }

    public function getEpisode(): Episode
    {
        return $this->episode;
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
        $timeRemaining = $this->episode->getStreamableUntil()->diff(Chronos::now());

        $string = 'iplayer_play_remaining';
        if ($this->episode->getMediaType()) {
            if ($this->episode->getMediaType() === 'audio') {
                $string = 'iplayer_listen_remaining';
            } else {
                $string = 'iplayer_watch_remaining';
            }
        } elseif ($this->episode->isRadio()) {
            $string = 'iplayer_listen_remaining';
        } elseif ($this->episode->isTv()) {
            $string = 'iplayer_watch_remaining';
        }
        return $this->playTranslationsHelper->timeIntervalToWords($timeRemaining, false, $string);
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

    public function getPodcastUrls(): array
    {
        $mediaSets = $this->episode->getDownloadableMediaSets();
        if (empty($mediaSets)) {
            return [];
        }
        $versionPid = $this->getVersionPid();
        $urls = [];
        if (in_array('audio-nondrm-download', $mediaSets)) {
            $urls['podcast_128kbps_quality'] = $this->router->generate('podcast_download', ['pid' => $versionPid]);
        }
        if (in_array('audio-nondrm-download-low', $mediaSets)) {
            $urls['podcast_64kbps_quality'] = $this->router->generate('podcast_download_low', ['pid' => $versionPid]);
        }
        return $urls;
    }

    public function getPodcastFileName(): string
    {
        /** @var CoreEntity[] $ancestry */
        $ancestry = array_reverse($this->episode->getAncestry());
        $titles = [];
        foreach ($ancestry as $ancestor) {
            $titles[] = $ancestor->getTitle();
        }
        return implode(', ', $titles) . ' - ' . $this->episode->getPid() . '.mp3';
    }

    public function isUkOnlyPodcast(): bool
    {
        if ($this->rmsPodcast) {
            return $this->rmsPodcast->isOnlyInUk();
        }

        return false;
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

    private function getVersionPid(): Pid
    {
        foreach ($this->availableVersions as $version) {
            if ($version->isDownloadable()) {
                return $version->getPid();
            }
        }
        throw new Exception('No podcastable Versions were found');
    }
}
