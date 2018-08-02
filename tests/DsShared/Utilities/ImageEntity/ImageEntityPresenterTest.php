<?php
declare(strict_types = 1);
namespace Tests\App\DsShared\Utilities\ImageEntity;

use App\Builders\ImageBuilder;
use App\DsShared\Utilities\ImageEntity\ImageEntityPresenter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ImageEntityPresenterTest extends TestCase
{
    public function testImageEntityPresenter(): void
    {
        $image = ImageBuilder::any()->build();
        $sizes = [130 => 1/2];
        $imagePresenter = new ImageEntityPresenter($image, 48, $sizes);

        // Test default options
        $this->assertEquals(true, $imagePresenter->getOption('is_lazy_loaded'));
        $this->assertEquals([80, 160, 320, 480, 640, 768, 896, 1008], $imagePresenter->getOption('srcsets'));
        $this->assertEquals('', $imagePresenter->getOption('alt'));
        $this->assertEquals((16 / 9), $imagePresenter->getOption('ratio'));
        $this->assertEquals(false, $imagePresenter->getOption('is_bounded'));

        // Test generating src url using the defaultWidth argument
        $this->assertEquals('https://ichef.bbci.co.uk/images/ic/48x27/' . $image->getPid() . '.jpg', $imagePresenter->getSrc());
    }

    public function testSettingOptions(): void
    {
        $image = ImageBuilder::any()->build();
        $sizes = [130 => 1/2];

        $imagePresenter = new ImageEntityPresenter($image, 300, $sizes, [
            'srcsets' => [320],
            'alt' => 'alt text',
            'ratio' => 1/2,
            'is_bounded' => true,
        ]);

        $this->assertEquals('(min-width: 8.125em) 50vw, 100vw', $imagePresenter->getSizes());
        $this->assertEquals('https://ichef.bbci.co.uk/images/ic/300x600_b/' . $image->getPid() . '.jpg', $imagePresenter->getSrc());
        $this->assertEquals('https://ichef.bbci.co.uk/images/ic/320x640_b/' . $image->getPid() . '.jpg 320w', $imagePresenter->getSrcsets());
    }

    public function testSizesStringOverride(): void
    {
        $image = ImageBuilder::any()->build();
        $imagePresenter = new ImageEntityPresenter($image, 48, 'string override');

        $this->assertEquals('string override', $imagePresenter->getSizes());
    }

    public function testEmptySizesArray(): void
    {
        $image = ImageBuilder::any()->build();
        $imagePresenter = new ImageEntityPresenter($image, 48, []);

        $this->assertEquals('100vw', $imagePresenter->getSizes());
    }

    public function testInvalidSizesType()
    {
        $image = ImageBuilder::any()->build();

        $this->expectException(InvalidArgumentException::class);
        new ImageEntityPresenter($image, 48, 3);
    }
}
