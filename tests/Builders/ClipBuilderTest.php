<?php
namespace Tests\App\Builders;

use App\Builders\ClipBuilder;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use PHPUnit\Framework\TestCase;

class ClipBuilderTest extends TestCase
{
    public function testBasicSeriesCanBeCreated()
    {
        $series = ClipBuilder::any()->build();

        $this->assertInstanceOf(Clip::class, $series);
    }

    public function testComplexSeriesCanBeCreated()
    {
        $series = ClipBuilder::any()->with(['position' => 1600, 'relatedLinksCount' => '100'])->build();

        $this->assertEquals(1600, $series->getPosition());
        $this->assertEquals(100, $series->getRelatedLinksCount());
    }

    public function testOptionalsValuesStayToDefault()
    {
        $series = ClipBuilder::any()->with(['position' => 1600])->build();

        $this->assertNull($series->getFirstBroadcastDate());
    }

    public function testSeriesCreatedHaveFixedValues()
    {
        $series1 = ClipBuilder::any()->with(['isStreamable' => false])->build();
        $series2 = ClipBuilder::any()->with(['isStreamable' => true])->build();

        $this->assertEquals(false, $series1->isStreamable());
        $this->assertEquals(true, $series2->isStreamable());
    }

    /**
     * @expectedException \Exception
     */
    public function testExceptionIsRaisedWhenInvalidOptionIsPassed()
    {
        ClipBuilder::any()->with(['wrongOption' => 10])->build();
    }

    /**
     * @expectedException \Exception
     */
    public function testExceptionIsRaisedAlsoIfDifferentCapitalizationIsUsed()
    {
        ClipBuilder::any()->with(['Isstremable' => true])->build();
    }
}
