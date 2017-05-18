<?php
declare(strict_types = 1);
namespace Tests\App\Ds2013\Molecule\Image;

use App\Ds2013\Exception\InvalidOptionException;
use App\Ds2013\Molecule\Image\ImagePresenter;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class ImagePresenterTest extends TestCase
{
    public function testImagePresenter(): void
    {
        $image = $this->createMockImage();
        $sizes = [130 => 1/2];
        $imagePresenter = new ImagePresenter($image, $sizes);

        // Test default options
        $this->assertEquals(true, $imagePresenter->getOption('is_lazy_loaded'));
        $this->assertEquals([320, 480, 640, 768, 896, 1008], $imagePresenter->getOption('srcsets'));
        $this->assertEquals('', $imagePresenter->getOption('alt'));
        $this->assertNull($imagePresenter->getOption('ratio'));
        $this->assertNull($imagePresenter->getOption('src_width'));

        // Test generating src url using the first srcset
        $this->assertEquals('320 by n', $imagePresenter->getSrc());
    }

    public function testSettingOptions(): void
    {
        $image = $this->createMockImage();
        $sizes = [130 => 1/2];

        $imagePresenter = new ImagePresenter($image, $sizes, [
            'srcsets' => [320],
            'alt' => 'alt text',
            'src_width' => 300,
            'ratio' => 1/2,
        ]);

        $this->assertEquals('(min-width: 8.125em) 50vw, 100vw', $imagePresenter->getSizes());
        $this->assertEquals('300 by 600', $imagePresenter->getSrc());
        $this->assertEquals('320 by 640 320w', $imagePresenter->getSrcsets());
    }

    public function testSizesStringOverride(): void
    {
        $image = $this->createMockImage();
        $sizes = 'string override';
        $imagePresenter = new ImagePresenter($image, $sizes);

        $this->assertEquals('string override', $imagePresenter->getSizes());
    }

    public function testEmptySizesArray(): void
    {
        $image = $this->createMockImage();
        $sizes = [];
        $imagePresenter = new ImagePresenter($image, $sizes);

        $this->assertEquals('100vw', $imagePresenter->getSizes());
    }

    public function testInvalidSizesType()
    {
        $image = $this->createMockImage();
        $sizes = 3;

        $this->expectException(InvalidArgumentException::class);
        new ImagePresenter($image, $sizes);
    }

    public function testInvalidSrcsetsAndSrcWidthCombination()
    {
        $image = $this->createMockImage();
        $sizes = [];

        $this->expectException(InvalidOptionException::class);
        new ImagePresenter($image, $sizes, [
            'srcsets' => [],
            'src_width' => null,
        ]);
    }

    private function createMockImage()
    {
        $image = $this->createMock('BBC\ProgrammesPagesService\Domain\Entity\Image');
        $image->method('getUrl')
            ->will($this->returnCallback(function ($width, $height) {
                return $width . ' by ' . $height;
            }));
        return $image;
    }
}
