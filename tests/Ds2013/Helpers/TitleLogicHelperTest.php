<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Helpers;

use App\Ds2013\Helpers\TitleLogicHelper;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use PHPUnit\Framework\TestCase;

class TitleLogicHelperTest extends TestCase
{
    /** @var TitleLogicHelper */
    private $helper;

    public function setUp()
    {
        $this->helper = new TitleLogicHelper();
    }

    /** @expectedException \InvalidArgumentException */
    public function testInvalidTitleFormat()
    {
        $programme = $this->createMock(Programme::class);
        $this->helper->getOrderedProgrammesForTitle($programme, null, 'bark::woof::meow');
    }

    public function testNoAncestry()
    {
        $programme = $this->buildProgrammeWithAncestry(1);

        list ($mainTitleProgramme, $subTitlesProgrammes) = $this->helper->getOrderedProgrammesForTitle($programme);
        $this->assertSame($programme, $mainTitleProgramme);
        $this->assertCount(0, $subTitlesProgrammes);
    }

    public function testItemAncestryOrderingShort()
    {
        $programme = $this->buildProgrammeWithAncestry(2);
        list ($mainTitleProgramme, $subTitlesProgrammes) =
            $this->helper->getOrderedProgrammesForTitle($programme, null, 'item::ancestry');

        $this->assertEquals('depth_0', $mainTitleProgramme->getTitle());
        $this->assertCount(1, $subTitlesProgrammes);
        $this->assertEquals('depth_1', $subTitlesProgrammes[0]->getTitle());
    }

    public function testItemAncestryOrderingLong()
    {
        $programme = $this->buildProgrammeWithAncestry(4);
        list ($mainTitleProgramme, $subTitlesProgrammes) =
            $this->helper->getOrderedProgrammesForTitle($programme, null, 'item::ancestry');

        $this->assertEquals('depth_0', $mainTitleProgramme->getTitle());
        $this->assertCount(3, $subTitlesProgrammes);
        $this->assertEquals('depth_3', $subTitlesProgrammes[0]->getTitle());
        $this->assertEquals('depth_2', $subTitlesProgrammes[1]->getTitle());
        $this->assertEquals('depth_1', $subTitlesProgrammes[2]->getTitle());
    }

    public function testTleoAncestryItemOrderingShort()
    {
        $programme = $this->buildProgrammeWithAncestry(2);
        list ($mainTitleProgramme, $subTitlesProgrammes) =
            $this->helper->getOrderedProgrammesForTitle($programme);

        $this->assertEquals('depth_1', $mainTitleProgramme->getTitle());
        $this->assertCount(1, $subTitlesProgrammes);
        $this->assertEquals('depth_0', $subTitlesProgrammes[0]->getTitle());
    }

    public function testTleoAncestryItemOrderingLong()
    {
        $programme = $this->buildProgrammeWithAncestry(4);
        list ($mainTitleProgramme, $subTitlesProgrammes) =
            $this->helper->getOrderedProgrammesForTitle($programme, null, 'tleo::ancestry:item');

        $this->assertEquals('depth_3', $mainTitleProgramme->getTitle());
        $this->assertCount(3, $subTitlesProgrammes);
        $this->assertEquals('depth_2', $subTitlesProgrammes[0]->getTitle());
        $this->assertEquals('depth_1', $subTitlesProgrammes[1]->getTitle());
        $this->assertEquals('depth_0', $subTitlesProgrammes[2]->getTitle());
    }

    private function buildProgrammeWithAncestry(int $depth)
    {
        $programmes = [];
        for ($i = 0; $i < $depth; $i++) {
            $programme = $this->createMock(Programme::class);
            $programme->method('getTitle')->willReturn("depth_$i");
            $programmes[] = $programme;
        }
        $programmes[0]->method('getAncestry')->willReturn($programmes);
        return $programmes[0];
    }
}
