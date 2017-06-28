<?php
declare(strict_types = 1);
namespace App\Twig;

use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use InvalidArgumentException;
use Symfony\Component\Asset\Packages;
use Twig_Extension;
use Twig_Function;

class RdfaSchemaExtension extends Twig_Extension
{
    private $packages;

    public function __construct(Packages $packages)
    {
        $this->packages = $packages;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_Function('rdfa_schema_programme_typeof', [$this, 'rdfaSchemaProgrammeTypeOf']),
            new Twig_Function('rdfa_schema_mediaobject_typeof', [$this, 'rdfaSchemaMediaObjectTypeOf']),
            new Twig_Function('rdfa_schema_media_type', [$this, 'rdfaSchemaMediaType']),
        ];
    }

    public function rdfaSchemaProgrammeTypeOf($entity): string
    {
        switch ($entity) {
            case ($entity instanceof Brand):
                return $entity->isRadio() ? 'RadioSeries' : 'TVSeries';
            case ($entity instanceof Series):
                return $entity->isRadio() ? 'Season' : 'TVSeason';
            case ($entity instanceof Episode):
                return $entity->isRadio() ? 'RadioEpisode' : 'Episode';
            case ($entity instanceof Clip):
                return $entity->isRadio() ? 'RadioClip' : 'Clip';
            default:
                $type = is_object($entity) ? get_class($entity) : gettype($entity);
                throw new InvalidArgumentException('cannot get rdfa schema type for entity of type ' . $type);
        }
    }

    public function rdfaSchemaMediaObjectTypeOf($entity): string
    {
        switch ($entity) {
            case ($entity instanceof ProgrammeItem):
                return ($entity->isAudio() ? 'audio' : 'video');
            default:
                $type = is_object($entity) ? get_class($entity) : gettype($entity);
                throw new InvalidArgumentException('cannot get rdfa schema type for entity of type ' . $type);
        }
    }

    public function rdfaSchemaMediaType($entity): string
    {
        switch ($entity) {
            case ($entity instanceof ProgrammeItem):
                return ($entity->isAudio() ? 'AudioObject' : 'VideoObject');
            default:
                $type = is_object($entity) ? get_class($entity) : gettype($entity);
                throw new InvalidArgumentException('cannot get rdfa schema type for entity of type ' . $type);
        }
    }
}
