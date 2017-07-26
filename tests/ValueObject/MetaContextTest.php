<?php
declare(strict_types = 1);

namespace Tests\App\ValueObject;

use App\ValueObject\MetaContext;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use PHPUnit\Framework\TestCase;

class MetaContextTest extends TestCase
{
    public function testWithNullContext()
    {
        $metaContext = new MetaContext(null, 'url');

        $this->assertSame('', $metaContext->description());
        $this->assertSame('', $metaContext->titlePrefix());
        $this->assertSame('url', $metaContext->canonicalUrl());
        $this->assertFalse($metaContext->isRadio());

        $this->assertEquals('https://ichef.bbci.co.uk/images/ic/1x1/p01tqv8z.png', $metaContext->image()->getUrl(1, 1));
    }

    public function testWithProgrammeContext()
    {
        $image = $this->createMock(Image::class);
        $image->method('getTitle')->willReturn('getTitleOutput');

        $network = $this->createMock(Network::class);
        $network->method('getName')->willReturn('The name');

        $programme = $this->createMock(Programme::class);
        $programme->method('getNetwork')->willReturn($network);
        $programme->method('getImage')->willReturn($image);
        $programme->method('getShortSynopsis')->willReturn('A short synopsis');
        $programme->method('isRadio')->willReturn(true);

        $metaContext = new MetaContext($programme);

        $this->assertEquals('A short synopsis', $metaContext->description());
        $this->assertEquals('The name', $metaContext->titlePrefix());
        $this->assertTrue($metaContext->isRadio());
        $this->assertEquals('getTitleOutput', $metaContext->image()->getTitle());
    }

    public function testWithServiceContext()
    {
        $image = $this->createMock(Image::class);
        $image->method('getTitle')->willReturn('getTitleOutput');

        $network = $this->createMock(Network::class);
        $network->method('getImage')->willReturn($image);

        $service = $this->createMock(Service::class);
        $service->method('getName')->willReturn('Geoff');
        $service->method('getNetwork')->willReturn($network);
        $service->method('isRadio')->willReturn(false);

        $metaContext = new MetaContext($service);

        $this->assertSame('', $metaContext->description());
        $this->assertSame('Geoff', $metaContext->titlePrefix());
        $this->assertFalse($metaContext->isRadio());
        $this->assertEquals('getTitleOutput', $metaContext->image()->getTitle());
    }
}
