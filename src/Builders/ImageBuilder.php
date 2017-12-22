<?php
declare(strict_types = 1);
namespace App\Builders;

use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Faker\Factory;

class ImageBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();

        $this->classTarget = Image::class;
        // configure order of params to use Image constructor. You are free to choose the key names, but no the order.
        $this->blueprintConstructorTarget = [
            'pid' => new Pid($faker->regexify('[0-9b-df-hj-np-tv-z]{8,15}')),
            'title' => $faker->sentence(3),
            'shortSynopsis' => $faker->sentence(5),
            'longestSynopsis' => $faker->sentence(30),
            'type' => 'standard',
            'extension' => 'jpg',
        ];
    }
}
