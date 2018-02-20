<?php

namespace App\Builders;

use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Options;
use BBC\ProgrammesPagesService\Domain\Enumeration\NetworkMediumEnum;
use BBC\ProgrammesPagesService\Domain\ValueObject\Nid;
use Faker\Factory;

class NetworkBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();

        $this->classTarget = Network::class;
        // configure order of params to use Network constructor. You are free to choose the key names, but not the order.
        $this->blueprintConstructorTarget = [
            'nid' => new Nid($faker->regexify('^[0-9a-z_]{1,5}$')),
            'name' => $faker->text,
            'image' => ImageBuilder::any()->build(),
            'options' => new Options(),
            'urlKey' => null,
            'type' => null,
            'medium' => NetworkMediumEnum::UNKNOWN,
            'defaultService' => null,
            'isPublicOutlet' => false,
            'isChildrens' => false,
            'isWorldServiceInternational' => false,
            'isInternational' => false,
            'isAllowedAdverts' => false,
            'services' => null,
        ];
    }
}
