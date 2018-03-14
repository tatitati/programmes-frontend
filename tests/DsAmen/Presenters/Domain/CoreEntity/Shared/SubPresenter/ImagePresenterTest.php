<?php
declare(strict_types=1);

namespace Tests\App\DsAmen\Presenters\Domain\CoreEntity\Shared\SubPresenter;

use App\DsAmen\Presenters\Domain\CoreEntity\Shared\SubPresenter\ImagePresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Tests\App\DsAmen\Presenters\Domain\CoreEntity\BaseSubPresenterTest;

class ImagePresenterTest extends BaseSubPresenterTest
{
    /** @var UrlGenerator|PHPUnit_Framework_MockObject_MockObject */
    private $mockRouter;

    /** @var  Episode|PHPUnit_Framework_MockObject_MockObject */
    private $programme;

    public function setUp()
    {
        $this->mockRouter = $this->createMock(UrlGenerator::class);
        $this->programme = $this->createMock(Episode::class);
    }

    public function testGetImageReturnsNullWhenOptionIsFalse(): void
    {
        $imagePresenter = new ImagePresenter(
            $this->programme,
            $this->mockRouter,
            null,
            ['show_image' => false]
        );
        $this->assertNull($imagePresenter->getImage());
    }

    public function testGetImageReturnsImageWhenOptionIsTrue(): void
    {
        $imagePresenter = new ImagePresenter(
            $this->programme,
            $this->mockRouter,
            null,
            ['show_image' => true]
        );
        $this->assertNotNull($imagePresenter->getImage());
    }

    /** @dataProvider showCtaProvider */
    public function testShowCta(CoreEntity $coreEntity, bool $expected): void
    {
        $imagePresenter = new ImagePresenter(
            $coreEntity,
            $this->mockRouter,
            null
        );

        $this->assertEquals($expected, $imagePresenter->showCta());
    }

    public function showCtaProvider(): array
    {
        $streamableEpisode = $this->createMock(Episode::class);
        $streamableEpisode->method('isStreamable')->willReturn(true);

        $nonStreamableEpisode = $this->createMock(Episode::class);
        $nonStreamableEpisode->method('isStreamable')->willReturn(false);

        $brand = $this->createMock(Brand::class);

        return [
            'Streamable ProgrammeItem shows CTA' => [$streamableEpisode, true],
            'Non-streamable ProgrammeItem does not show CTA' => [$nonStreamableEpisode, false],
            'Brand does not show CTA' => [$brand, false],
        ];
    }
}
