<?php
namespace Tests\App\Builders;

use App\Builders\ImageBuilder;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use PHPUnit\Framework\TestCase;

class ImageBuilderTest extends TestCase
{
    public function testBasicSeriesCanBeCreated()
    {
        $series = ImageBuilder::any()->build();

        $this->assertInstanceOf(Image::class, $series);
    }

    public function testComplexSeriesCanBeCreated()
    {
        $series = ImageBuilder::any()->with(['title' => 'my link title'])->build();

        $this->assertEquals('my link title', $series->getTitle());
        $this->assertEquals('standard', $series->getType());
    }
}
