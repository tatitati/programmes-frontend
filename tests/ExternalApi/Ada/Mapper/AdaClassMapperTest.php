<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Ada\Mapper;

use App\ExternalApi\Ada\Domain\AdaClass;
use App\ExternalApi\Ada\Mapper\AdaClassMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use PHPUnit\Framework\TestCase;

class AdaClassMapperTest extends TestCase
{
    public function testMapItem()
    {
        $adaEntity = [
            'id' => 'Fellows_of_the_Royal_Society',
            'type' => 'category',
            'title' => 'Fellows of the Royal Society',
            'image' => 'ichef.bbci.co.uk/images/ic/$recipe/p01l7xx5.jpg',
            'programme_items_count' => 16,
        ];

        $mapper = new AdaClassMapper();

        $expectedEntity = new AdaClass(
            'Fellows_of_the_Royal_Society',
            'Fellows of the Royal Society',
            16,
            new Pid('b0000001'),
            new Image(new Pid('p01l7xx5'), '', '', '', '', 'jpg')
        );

        $this->assertEquals($expectedEntity, $mapper->mapItem($adaEntity, 'b0000001'));
    }

    public function testMapItemWithNullContext()
    {
        $adaEntity = [
            'id' => 'Fellows_of_the_Royal_Society',
            'type' => 'category',
            'title' => 'Fellows of the Royal Society',
            'image' => 'ichef.bbci.co.uk/images/ic/$recipe/p01l7xx5.jpg',
            'programme_items_count' => 16,
        ];

        $mapper = new AdaClassMapper();

        $expectedEntity = new AdaClass(
            'Fellows_of_the_Royal_Society',
            'Fellows of the Royal Society',
            16,
            null,
            new Image(new Pid('p01l7xx5'), '', '', '', '', 'jpg')
        );

        $this->assertEquals($expectedEntity, $mapper->mapItem($adaEntity, null));
    }
}
