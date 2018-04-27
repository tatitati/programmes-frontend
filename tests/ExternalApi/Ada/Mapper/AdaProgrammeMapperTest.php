<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Ada\Mapper;

use App\ExternalApi\Ada\Domain\AdaClass;
use App\ExternalApi\Ada\Domain\AdaProgrammeItem;
use App\ExternalApi\Ada\Mapper\AdaClassMapper;
use App\ExternalApi\Ada\Mapper\AdaProgrammeMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use PHPUnit\Framework\TestCase;

class AdaProgrammeMapperTest extends TestCase
{
    public function testMapItemWithClassModels()
    {
        $programme = $this->createMock(Programme::class);

        $adaEntity = [
            'pid' => 'p03knff4',
            'title' => 'Fellows of the Royal Society',
            'class_count' => 16,
            'via' => [
                [
                    "id" => 'Maritime_history_of_the_Dutch_Republic',
                    "type" => 'category',
                    "title" => 'Maritime history of the Dutch Republic',
                    "image" => 'ichef.bbci.co.uk/images/ic/$recipe/p03knff4.jpg',
                    "programme_items_count" => 2,
                ],
            ],
        ];

        $mapper = new AdaProgrammeMapper(new AdaClassMapper());

        $pid = new Pid("p03knff4");

        $expectedClass = new AdaClass(
            "Maritime_history_of_the_Dutch_Republic",
            "Maritime history of the Dutch Republic",
            2,
            $pid,
            new Image(
                $pid,
                "",
                "",
                "",
                "",
                "jpg"
            )
        );
        $expectedEntity = new AdaProgrammeItem(
            $programme,
            'p03knff4',
            'Fellows of the Royal Society',
            16,
            [$expectedClass]
        );

        $this->assertEquals($expectedEntity, $mapper->mapItem($programme, $adaEntity));
    }

    public function testMapItemWithoutClassModels()
    {
        $programme = $this->createMock(Programme::class);

        $adaEntity = [
            'pid' => 'b071vl2l',
            'type' => 'category',
            'title' => 'Fellows of the Royal Society',
            'class_count' => 16,
        ];

        $mapper = new AdaProgrammeMapper(new AdaClassMapper());

        $expectedEntity = new AdaProgrammeItem(
            $programme,
            'b071vl2l',
            'Fellows of the Royal Society',
            16,
            null
        );

        $this->assertEquals($expectedEntity, $mapper->mapItem($programme, $adaEntity));
    }
}
