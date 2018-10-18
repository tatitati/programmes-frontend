<?php
namespace App\Ds2013\Presenters\Utilities\Download;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Podcast;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DownloadPresenter extends Presenter
{
    /** @var ProgrammeItem */
    private $programme;

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var Version */
    private $version;

    /** @var Podcast|null */
    private $podcast;

    public function __construct(UrlGeneratorInterface $router, ProgrammeItem $programme, Version $version, ?Podcast $podcast, array $options = [])
    {
        $this->programme = $programme;
        $this->router = $router;
        $this->version = $version;
        $this->podcast = $podcast;
        parent::__construct($options);
    }

    public function getProgramme(): ProgrammeItem
    {
        return $this->programme;
    }

    public function getPodcastUrls(): array
    {
        $mediaSets = $this->programme->getDownloadableMediaSets();
        if (empty($mediaSets)) {
            return [];
        }
        $versionPid = $this->version->getPid();
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
        $ancestry = array_reverse($this->programme->getAncestry());
        $titles = [];
        foreach ($ancestry as $ancestor) {
            $titles[] = $ancestor->getTitle();
        }
        return implode(', ', $titles) . ' - ' . $this->programme->getPid() . '.mp3';
    }

    public function isUkOnlyPodcast(): bool
    {
        if ($this->podcast) {
            return $this->podcast->getIsUkOnly();
        }

        return false;
    }
}
