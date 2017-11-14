<?php
declare(strict_types = 1);
namespace App\Builders;

use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;

class ImageBuilder implements BuilderInterface
{
    /** @var Pid */
    private $pid;

    /** @var string */
    private $title;

    /** @var string */
    private $shortSynopsis;

    /** @var string */
    protected $longestSynopsis;

    /** @var string */
    private $type;

    /** @var string */
    private $extension;

    private function __construct()
    {
        $this->pid = new Pid('b00755wz');
        $this->title = 'Image title';
        $this->shortSynopsis = 'This is an image-short synopsis';
        $this->longestSynopsis = 'This is an image-long synopsis and is a little longer';
        $this->type = 'this is the type';
        $this->extension = 'jpg';
    }

    public function withPid(string $pid)
    {
        $this->pid = new Pid($pid);
        return $this;
    }

    public function withTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }

    public function withShortSynopses(string $shortSynopses)
    {
        $this->shortSynopsis = $shortSynopses;
        return $this;
    }

    public function withLongestSynopsis(string $longestSynopses)
    {
        $this->shortSynopsis = $longestSynopses;
        return $this;
    }

    public function withType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    public function withExtension(string $extension)
    {
        $this->extension = $extension;
        return $this;
    }

    public static function default()
    {
        return new self();
    }

    public function build(): Image
    {
        return new Image(
            $this->pid,
            $this->title,
            $this->shortSynopsis,
            $this->longestSynopsis,
            $this->type,
            $this->extension
        );
    }
}
