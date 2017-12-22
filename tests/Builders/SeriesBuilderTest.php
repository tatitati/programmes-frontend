<?php
namespace Tests\App\Builders;

use App\Builders\SeriesBuilder;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use PHPUnit\Framework\TestCase;

class SeriesBuilderTest extends TestCase
{
    public function testBasicSeriesCanBeCreated()
    {
        $series = SeriesBuilder::any()->build();

        $this->assertInstanceOf(Series::class, $series);
    }

    public function testComplexSeriesCanBeCreated()
    {
        $series = SeriesBuilder::any()->with(['position' => 1600, 'title' => 'new title'])->build();

        $this->assertEquals(1600, $series->getPosition());
        $this->assertEquals('new title', $series->getTitle());
    }

    public function testOptionalsValuesStayToDefault()
    {
        $series = SeriesBuilder::any()->with(['position' => 1600])->build();

        $this->assertNull($series->getFirstBroadcastDate());
    }

    public function testSeriesCreatedHaveFixedValues()
    {
        $series1 = SeriesBuilder::any()->with(['isStreamable' => false])->build();
        $series2 = SeriesBuilder::any()->with(['isStreamable' => true])->build();

        $this->assertEquals(false, $series1->isStreamable());
        $this->assertEquals(true, $series2->isStreamable());
    }

    /**
     * @expectedException \Exception
     */
    public function testExceptionIsRaisedWhenInvalidOptionIsPassed()
    {
        SeriesBuilder::any()->with(['wrongOption' => 10])->build();
    }

    /**
     * @expectedException \Exception
     */
    public function testExceptionIsRaisedAlsoIfDifferentCapitalizationIsUsed()
    {
        SeriesBuilder::any()->with(['Isstremable' => true])->build();
    }
}
