<?php
declare(strict_types = 1);
namespace App\Builders;

use BBC\ProgrammesPagesService\Domain\Entity\Promotion;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use Faker;

class PromotionBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Faker\Factory::create();

        $this->classTarget = Promotion::class;
        // configure order of params to use Promotion constructor. You are free to choose the key names, but no the order.
        $this->blueprintConstructorTarget = [
            'pid' => new Pid($faker->regexify('[0-9b-df-hj-np-tv-z]{8,15}')),
            'promotedEntity' => ImageBuilder::any(),
            'title' => $faker->sentence(3),
            'synopses' => new Synopses($faker->sentence(5), $faker->sentence(15), $faker->sentence(30)),
            'url' => $faker->url,
            'weighting' => $faker->numberBetween(1, 5),
            'isSuperPromotion' => $faker->boolean,
            'relatedLinks' => [
                RelatedLinkBuilder::externalLink(),
                RelatedLinkBuilder::internalLink(),
            ],
        ];
    }

    public static function ofImage()
    {
        $self = new self();
        return $self->anyWith([
          'promotedEntity' => ImageBuilder::any(),
        ]);
    }

    public static function ofCoreEntity()
    {
        $self = new self();
        return $self->anyWith([
            'promotedEntity' => EpisodeBuilder::any(),
        ]);
    }
}
