<?php
namespace Tests\App\Builders;

use App\Builders\BrandBuilder;
use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use PHPUnit\Framework\TestCase;

class BrandBuilderTest extends TestCase
{
    public function testBasicSeriesCanBeCreated()
    {
        $series = BrandBuilder::any()->build();

        $this->assertInstanceOf(Brand::class, $series);
    }

    public function testComplexSeriesCanBeCreated()
    {
        $series = BrandBuilder::any()->with(['position' => 1600, 'relatedLinksCount' => 100])->build();

        $this->assertEquals(1600, $series->getPosition());
        $this->assertEquals(100, $series->getRelatedLinksCount());
    }

    public function testOptionalsValuesStayToDefault()
    {
        $series = BrandBuilder::any()->with(['position' => 1600])->build();

        $this->assertNull($series->getFirstBroadcastDate());
    }

    public function testSeriesCreatedHaveFixedValues()
    {
        $series1 = BrandBuilder::any()->with(['isStreamable' => false])->build();
        $series2 = BrandBuilder::any()->with(['isStreamable' => true])->build();

        $this->assertEquals(false, $series1->isStreamable());
        $this->assertEquals(true, $series2->isStreamable());
    }

    public function testDelayCreationOfObject()
    {
        $builder = BrandBuilder::any()
            ->with(['isStreamable' => false])
            ->with(['position' => 99999]);
        $builder->with(['relatedLinksCount' => '100']);
        $brand = $builder->build();

        $this->assertEquals(false, $brand->isStreamable());
        $this->assertEquals(99999, $brand->getPosition());
        $this->assertEquals(100, $brand->getRelatedLinksCount());
    }

    /**
     * @expectedException \Exception
     */
    public function testExceptionIsRaisedWhenInvalidOptionIsPassed()
    {
        BrandBuilder::any()->with(['wrongOption' => 10])->build();
    }

    /**
     * @expectedException \Exception
     */
    public function testExceptionIsRaisedAlsoIfDifferentCapitalizationIsUsed()
    {
        BrandBuilder::any()->with(['Isstremable' => true])->build();
    }
}
