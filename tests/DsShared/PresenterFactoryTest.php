<?php
declare(strict_types = 1);
namespace Tests\App\DsShared;

use App\DsShared\Helpers\HelperFactory;
use App\DsShared\Molecule\Image\ImagePresenter;
use App\DsShared\PresenterFactory;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use PHPUnit\Framework\TestCase;
use RMP\Translate\Translate;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @covers App\DsShared\PresenterFactory
 */
class PresenterFactoryTest extends TestCase
{
    /** @var Translate */
    private $translate;

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var HelperFactory */
    private $helperFactory;

    /** @var PresenterFactory */
    private $factory;

    public function setUp()
    {
        $this->translate = $this->createMock(Translate::class);
        $translateProvider = $this->createMock(TranslateProvider::class);
        $translateProvider->method('getTranslate')->willReturn($this->translate);
        $this->router = $this->createMock(UrlGeneratorInterface::class);
        $this->helperFactory = $this->createMock(HelperFactory::class);
        $this->factory = new PresenterFactory($translateProvider, $this->router, $this->helperFactory);
    }

    public function testOrganismProgramme()
    {
        $mockImage = $this->createMock(Image::class);

        $this->assertEquals(
            new ImagePresenter($mockImage, 240, '1'),
            $this->factory->imagePresenter($mockImage, 240, '1')
        );
    }
}
