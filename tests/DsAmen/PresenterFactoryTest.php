<?php
declare(strict_types = 1);
namespace Tests\App\DsAmen;

use App\DsAmen\Organism\Programme\ProgrammePresenter;
use App\DsAmen\PresenterFactory;
use App\DsShared\Helpers\HelperFactory;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use PHPUnit\Framework\TestCase;
use RMP\Translate\Translate;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @covers App\DsAmen\PresenterFactory
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
        $mockProgramme = $this->createMock(Programme::class);

        $this->assertEquals(
            new ProgrammePresenter($mockProgramme, ['opt' => 'foo']),
            $this->factory->programmePresenter($mockProgramme, ['opt' => 'foo'])
        );
    }
}
