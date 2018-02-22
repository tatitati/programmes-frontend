<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Presenters\Domain\Promotion;

use App\Ds2013\Presenters\Domain\Promotion\PromotionPresenter;
use App\Exception\InvalidOptionException;
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
        ]);

        $promotion = $this->createConfiguredMock(Promotion::class, [
            'getPromotedEntity' => $promotedEntity,
        ]);

        $presenter = new PromotionPresenter($this->mockRouter, $promotion);
        $this->assertSame('/programmes/b0000001', $presenter->getUrl());
        $this->assertSame($image, $presenter->getImage());
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
    }

    public function testFilteringRelatedLinks()
    {
        $mockRelatedLinks = [
            $this->createMock(RelatedLink::class),
            $this->createMock(RelatedLink::class),
            $this->createMock(RelatedLink::class),
        ];

        $promotion = $this->createConfiguredMock(Promotion::class, [
            'getRelatedLinks' => $mockRelatedLinks,
        ]);

        $presenter = new PromotionPresenter($this->mockRouter, $promotion, [
            'show_synopsis' => false,
            'show_image' => false,
            'related_links_count' => 1,
        ]);

        $this->assertSame('', $presenter->getSynopsis());
        $this->assertSame([$mockRelatedLinks[0]], $presenter->getRelatedLinks());
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
            'show_image' => false,
            'related_links_count' => 0,
        ]);

        $this->assertSame('', $presenter->getSynopsis());
        $this->assertSame([], $presenter->getRelatedLinks());
        $this->assertNull($presenter->getImage());
    }

    public function testInvalidRelatedLinksCount()
    {
        $promotion = $this->createMock(Promotion::class);

        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('related_links_count option must 0 or a positive integer');

        new PromotionPresenter($this->mockRouter, $promotion, [
            'related_links_count' => -1,
        ]);
    }
}
