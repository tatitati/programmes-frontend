<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Utilities\Synopsis;

use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;

class SynopsisPresenter extends Presenter
{
    /** @var string */
    private $id;

    /** @var int */
    private $maxLength;

    /** @var Synopses */
    private $synopses;

    public function __construct(Synopses $synopses, int $maxLength, array $options = [])
    {
        parent::__construct($options);
        $this->synopses = $synopses;
        $this->maxLength = $maxLength;
        $this->id = $options['prefix'] ?? uniqid('synopsis-', false);
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string[]
     */
    public function getLongestSynopsis(): array
    {
        return $this->sanitiseText($this->synopses->getLongestSynopsis());
    }

    /**
     * Returns the medium synopsis if it's short enough, otherwise the short synopsis
     * @return string[]
     */
    public function getShortSynopsis(): array
    {
        $mediumSynopsis = $this->synopses->getMediumSynopsis();
        if (!empty($mediumSynopsis) && $this->countWithoutTags($mediumSynopsis) <= $this->maxLength) {
            return $this->sanitiseText($mediumSynopsis);
        }
        return $this->sanitiseText($this->synopses->getShortSynopsis());
    }

    public function needsShorterSynopsis(): bool
    {
        return $this->countWithoutTags($this->synopses->getLongestSynopsis()) > $this->maxLength;
    }

    private function countWithoutTags(string $text): int
    {
        return mb_strlen(strip_tags($text));
    }

    /**
     * @param string $text
     * @return string[] An array of strings. Each string denotes a paragraph. Strings may include <br/> tags
     */
    private function sanitiseText(string $text): array
    {
        $text = strip_tags($text);
        $text = htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
        $html = nl2br($text);
        $html = trim(preg_replace('/\s+/', ' ', $html)); // remove left over new lines
        $html = str_replace('/> ', '/>', $html); // remove space between tags
        return preg_split('/(<br \/>){2,}/', $html, -1, PREG_SPLIT_NO_EMPTY); //Paragraphs are defined by more than 1 br/new line
    }
}
