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
        $sizes = [130 => 1/2];
        $imagePresenter = new ImagePresenter('bcdf1234', 48, $sizes);

        // Test default options
        $this->assertEquals(true, $imagePresenter->getOption('is_lazy_loaded'));
        $this->assertEquals([80, 160, 320, 480, 640, 768, 896, 1008], $imagePresenter->getOption('srcsets'));
        $this->assertEquals('', $imagePresenter->getOption('alt'));
        $this->assertEquals((16 / 9), $imagePresenter->getOption('ratio'));
        $this->assertEquals(false, $imagePresenter->getOption('is_bounded'));

        // Test generating src url using the defaultWidth argument
        $this->assertEquals('https://ichef.bbci.co.uk/images/ic/48x27/bcdf1234.jpg', $imagePresenter->getSrc());
    }

    public function testSettingOptions(): void
    {
        $sizes = [130 => 1/2];

        $imagePresenter = new ImagePresenter('bcdf1234', 300, $sizes, [
            'srcsets' => [320],
            'alt' => 'alt text',
            'ratio' => 1/2,
            'is_bounded' => true,
        ]);

        $this->assertEquals('(min-width: 8.125em) 50vw, 100vw', $imagePresenter->getSizes());
        $this->assertEquals('https://ichef.bbci.co.uk/images/ic/300x600_b/bcdf1234.jpg', $imagePresenter->getSrc());
        $this->assertEquals('https://ichef.bbci.co.uk/images/ic/320x640_b/bcdf1234.jpg 320w', $imagePresenter->getSrcsets());
    }

    public function testSizesStringOverride(): void
    {
        $imagePresenter = new ImagePresenter('bcdf1234', 48, 'string override');

        $this->assertEquals('string override', $imagePresenter->getSizes());
    }

    public function testEmptySizesArray(): void
    {
        $imagePresenter = new ImagePresenter('bcdf1234', 48, []);

        $this->assertEquals('100vw', $imagePresenter->getSizes());
    }

    public function testInvalidSizesType()
    {
        $this->expectException(InvalidArgumentException::class);
        new ImagePresenter('bcdf1234', 48, 3);
    }
}
