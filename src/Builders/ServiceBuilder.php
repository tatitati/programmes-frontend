<?php

namespace App\Builders;

use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Sid;
use Faker\Factory;

class ServiceBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();

        $this->classTarget = Service::class;
        // configure order of params to use Service constructor. You are free to choose the key names, but not the order.
        $this->blueprintConstructorTarget = [
            'id' => $faker->numberBetween(1, 12345),
            'sid' => new Sid($faker->regexify('^[0-9a-z_]{1,5}$')),
            'pid' => new Pid($faker->regexify('[0-9b-df-hj-np-tv-z]{8,15}')),
            'name' => $faker->text,
            'shortName' => null,
            'urlKey' => null,
            'network' => null,
            'startDate' => null,
            'endDate' => null,
            'liveStreamUrl' => null,
        ];
    }
}
