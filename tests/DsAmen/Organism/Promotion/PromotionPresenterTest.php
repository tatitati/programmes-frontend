<?php
declare(strict_types = 1);

namespace Tests\App\DsAmen\Organism\Promotion;

use App\DsAmen\Organism\Promotion\PromotionPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\Promotion;
use BBC\ProgrammesPagesService\Domain\Entity\RelatedLink;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PromotionPresenterTest extends TestCase
{
    private $mockRouter;

    public function setUp()
    {
        $this->mockRouter = $this->createMock(UrlGeneratorInterface::class);
    }

    public function testPromotionDefaults()
    {
        $relatedLinks = [$this->createMock(RelatedLink::class)];

        $promotion = $this->createConfiguredMock(Promotion::class, [
            'getTitle' => 'title',
            'getShortSynopsis' => 'short synopsis',
            'getRelatedLinks' => $relatedLinks,
        ]);

        $presenter = new PromotionPresenter($this->mockRouter, $promotion);

        $this->assertSame('title', $presenter->getTitle());
        $this->assertSame('short synopsis', $presenter->getSynopsis());
        $this->assertSame($relatedLinks, $presenter->getRelatedLinks());
        $this->assertSame(0, $presenter->getDuration());
        $this->assertSame('br-box-subtle', $presenter->getBrandingBoxClass());
        $this->assertSame('br-subtle-text-ontext', $presenter->getTextBrandingClass());
    }

    public function testPromotionOfProgrammeItem()
    {
        $this->mockRouter->method('generate')
            ->with('find_by_pid', ['pid' => 'b0000001'])
            ->willReturn('/programmes/b0000001');

        $image = $this->createMock(Image::class);

        $promotedEntity = $this->createConfiguredMock(Episode::class, [
            'getPid' => new Pid('b0000001'),
            'getImage' => $image,
            'getDuration' => 11,
            'isStreamable' => false,
            'isTv' => false,
        ]);

        $promotion = $this->createConfiguredMock(Promotion::class, [
            'getPromotedEntity' => $promotedEntity,
        ]);

        $presenter = new PromotionPresenter($this->mockRouter, $promotion);
        $this->assertSame('/programmes/b0000001', $presenter->getUrl());
        $this->assertSame($image, $presenter->getImage());
        $this->assertSame(11, $presenter->getDuration());
        $this->assertSame([], $presenter->getActionIcon());
    }

    public function testPromotionOfStreamableProgrammeItem()
    {
        $this->mockRouter->method('generate')
            ->with('find_by_pid', ['pid' => 'b0000001'])
            ->willReturn('/programmes/b0000001');

        $image = $this->createMock(Image::class);

        $promotedEntity = $this->createConfiguredMock(Episode::class, [
            'getPid' => new Pid('b0000001'),
            'getImage' => $image,
            'getDuration' => 11,
            'isStreamable' => true,
            'isTv' => true,
        ]);

        $promotion = $this->createConfiguredMock(Promotion::class, [
            'getPromotedEntity' => $promotedEntity,
        ]);

        $presenter = new PromotionPresenter($this->mockRouter, $promotion);
        $this->assertSame('/programmes/b0000001', $presenter->getUrl());
        $this->assertSame($image, $presenter->getImage());
        // TV Episodes don't show a duration
        $this->assertSame(0, $presenter->getDuration());
        $this->assertSame(['set' => 'audio-visual', 'icon' => 'play'], $presenter->getActionIcon());
    }

    public function testPromotionOfImage()
    {
        $promotedEntity = $this->createMock(Image::class);

        $promotion = $this->createConfiguredMock(Promotion::class, [
            'getPromotedEntity' => $promotedEntity,
            'getUrl' => 'http://example.com',
        ]);

        $presenter = new PromotionPresenter($this->mockRouter, $promotion);

        $this->assertSame('http://example.com', $presenter->getUrl());
        $this->assertSame($promotedEntity, $presenter->getImage());
        $this->assertSame(['set' => 'basics', 'icon' => 'external'], $presenter->getActionIcon());

        // Test internal link

        $promotion = $this->createConfiguredMock(Promotion::class, [
            'getPromotedEntity' => $promotedEntity,
            'getUrl' => 'http://bbc.co.uk/internal',
        ]);

        $presenter = new PromotionPresenter($this->mockRouter, $promotion);
        $this->assertSame('http://bbc.co.uk/internal', $presenter->getUrl());
        $this->assertSame([], $presenter->getActionIcon());
    }

    public function testDisabledOptions()
    {
        $promotion = $this->createConfiguredMock(Promotion::class, [
            'getTitle' => 'title',
            'getShortSynopsis' => 'short synopsis',
            'getPromotedEntity' => $this->createMock(Image::class),
            'getRelatedLinks' => [$this->createMock(RelatedLink::class)],
        ]);

        $presenter = new PromotionPresenter($this->mockRouter, $promotion, [
            'show_synopsis' => false,
            'show_related_links' => false,
            'show_image' => false,
        ]);

        $this->assertSame('', $presenter->getSynopsis());
        $this->assertSame([], $presenter->getRelatedLinks());
        $this->assertNull($presenter->getImage());
    }
}
