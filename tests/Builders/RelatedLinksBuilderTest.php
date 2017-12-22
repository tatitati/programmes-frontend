<?php
namespace Tests\App\Builders;

use App\Builders\RelatedLinkBuilder;
use BBC\ProgrammesPagesService\Domain\Entity\RelatedLink;
use PHPUnit\Framework\TestCase;

class RelatedLinksBuilderTest extends TestCase
{
    public function testBasicSeriesCanBeCreated()
    {
        $series = RelatedLinkBuilder::any()->build();

        $this->assertInstanceOf(RelatedLink::class, $series);
    }

    public function testComplexSeriesCanBeCreated()
    {
        $series = RelatedLinkBuilder::any()->with(['title' => 'my link title', 'isExternal' => true])->build();

        $this->assertEquals('my link title', $series->getTitle());
        $this->assertTrue($series->isExternal());
    }
}
