<?php
declare(strict_types=1);

namespace App\ExternalApi\Tupac\Domain;

class Record
{
    /** @var string */
    private $recordId;

    /** @var string */
    private $title;

    /** @var string */
    private $artist;

    /** @var string */
    private $artistGid;

    /** @var int|null milliseconds */
    private $duration;

    /** @var string url to mp3 */
    private $resource;

    /** @var string Tupac only return MP3 or CLIP */
    private $format;

    /** @var string can only be FULL_TRACK or SNIPPET */
    private $audioType;

    /** @var string */
    private $imagePid;

    public function __construct(
        string $recordId,
        string $title = '',
        string $artist = '',
        string $artistGid = '',
        string $imagePid = '',
        ?int $duration = null,
        string $resource = '',
        string $format = '',
        string $audioType = ''
    ) {
        $this->recordId = $recordId;
        $this->title = $title;
        $this->artist = $artist;
        $this->artistGid = $artistGid;
        $this->imagePid = $imagePid;
        $this->duration = $duration;
        $this->resource = $resource;
        $this->format = $format;
        $this->audioType = $audioType;
    }

    /**
     * @return string
     */
    public function getRecordId(): string
    {
        return $this->recordId;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getArtist(): string
    {
        return $this->artist;
    }

    /**
     * @return string
     */
    public function getArtistGid(): string
    {
        return $this->artistGid;
    }

    /**
     * @return int milliseconds
     */
    public function getDuration(): int
    {
        return empty($this->duration) ? 0 : $this->duration;
    }

    /**
     * @return string
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * @return string
     */
    public function getBase64Resource(): string
    {
        return base64_encode($this->resource);
    }

    /**
     * @return string Tupac only return MP3 or CLIP
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @return string can only be FULL_TRACK or SNIPPET
     */
    public function getAudioType(): string
    {
        return $this->audioType;
    }

    /**
     * @return string
     */
    public function getimagePid(): string
    {
        return $this->imagePid;
    }

    public function getImageSrc(): string
    {
        if (!empty($this->imagePid)) {
            return 'https://ichef.bbc.co.uk/images/ic/96x96/' . $this->imagePid . '.jpg';
        }
        return '';
    }

    /**
     * Return duration in the format 0:30, same way as the previous snippet backend did it
     *
     * @return string
     */
    public function getFormattedDuration(): string
    {
        if (!empty($this->duration) && $this->duration > 0) {
            // "floor" return an integer value as a float so it's necessary to cast it to integer
            $durationInSeconds = (int) floor($this->duration / 1000);
            $formattedDuration = intval(gmdate("i", $durationInSeconds)) . gmdate(":s", $durationInSeconds);
            return $formattedDuration;
        }
        return '';
    }

    public function getTitleTag(): string
    {
        if (empty($this->resource) || empty($this->format)) {
            $titleTag = 'This snippet is currently unavailable';
        } elseif (!empty($this->title)) {
            $titleTag = 'Play ' . $this->title;
        } else {
            $titleTag =  'Play audio';
        }
        return $titleTag;
    }
}
