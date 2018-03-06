<?php
declare(strict_types = 1);
namespace Tests\App\DsShared\Utilities\Image;

use App\DsShared\Utilities\Image\ImagePresenter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ImagePresenterTest extends TestCase
{
    public function testImagePresenter(): void
    {
        $image = $this->createMockImage();
        $sizes = [130 => 1/2];
        $imagePresenter = new ImagePresenter($image, 48, $sizes);

        // Test default options
        $this->assertEquals(true, $imagePresenter->getOption('is_lazy_loaded'));
        $this->assertEquals([80, 160, 320, 480, 640, 768, 896, 1008], $imagePresenter->getOption('srcsets'));
        $this->assertEquals('', $imagePresenter->getOption('alt'));
        $this->assertEquals((16 / 9), $imagePresenter->getOption('ratio'));
        $this->assertEquals(false, $imagePresenter->getOption('is_bounded'));

        // Test generating src url using the defaultWidth argument
        $this->assertEquals('48 by 27', $imagePresenter->getSrc());
    }

    public function testSettingOptions(): void
    {
        $image = $this->createMockImage();
        $sizes = [130 => 1/2];

        $imagePresenter = new ImagePresenter($image, 300, $sizes, [
            'srcsets' => [320],
            'alt' => 'alt text',
            'ratio' => 1/2,
            'is_bounded' => true,
        ]);

        $this->assertEquals('(min-width: 8.125em) 50vw, 100vw', $imagePresenter->getSizes());
        $this->assertEquals('300 by 600_b', $imagePresenter->getSrc());
        $this->assertEquals('320 by 640_b 320w', $imagePresenter->getSrcsets());
    }

    public function testSizesStringOverride(): void
    {
        $image = $this->createMockImage();
        $imagePresenter = new ImagePresenter($image, 48, 'string override');

        $this->assertEquals('string override', $imagePresenter->getSizes());
    }

    public function testEmptySizesArray(): void
    {
        $image = $this->createMockImage();
        $imagePresenter = new ImagePresenter($image, 48, []);

        $this->assertEquals('100vw', $imagePresenter->getSizes());
    }

    public function testInvalidSizesType()
    {
        $image = $this->createMockImage();

        $this->expectException(InvalidArgumentException::class);
        new ImagePresenter($image, 48, 3);
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
