<?php
namespace App\Builders;

use BBC\ProgrammesPagesService\Domain\Entity\RelatedLink;
use Faker\Factory;

class RelatedLinkBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();

        $this->classTarget = RelatedLink::class;
        // configure order of params to use RelatedLink constructor. You are free to choose the key names, but no the order.
        $this->blueprintConstructorTarget = [
            'title' => $faker->sentence(3),
            'uri' => $faker->url,
            'shortSynopsis' => $faker->sentence(5),
            'longestSynopsis' => $faker->sentence(30),
            'type' => $faker->text,
            'isExternal' => $faker->boolean,
        ];
    }

    public static function internalLink()
    {
        $self = new self();
        return $self->with([
            'uri' => 'https://www.bbc.co.uk/something',
            'isExternal' => false,
        ]);
    }

    public static function externalLink()
    {
        $faker = Factory::create();

        $self = new self();
        return $self->with([
            'uri' => $faker->url,
            'isExternal' => true,
        ]);
    }
}
