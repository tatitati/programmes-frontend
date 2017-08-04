<?php
declare(strict_types = 1);
namespace Tests\App\Ds2013;

use App\DsShared\Helpers\HelperFactory;
use App\Ds2013\Molecule\Calendar\CalendarPresenter;
use App\Ds2013\Molecule\DateList\DateListPresenter;
use App\Ds2013\Organism\Broadcast\BroadcastPresenter;
use App\Ds2013\Organism\Programme\ProgrammePresenter;
use App\Ds2013\PresenterFactory;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;
use RMP\Translate\Translate;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @covers App\Ds2013\PresenterFactory
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

    public function testOrganismBroadcast()
    {
        $mockBroadcast = $this->createMock(Broadcast::class);

        $this->assertEquals(
            new BroadcastPresenter($mockBroadcast, null, ['opt' => 'foo']),
            $this->factory->broadcastPresenter($mockBroadcast, null, ['opt' => 'foo'])
        );
    }

    public function testOrganismProgramme()
    {
        $mockProgramme = $this->createMock(Programme::class);

        $this->assertEquals(
            new ProgrammePresenter($this->router, $this->helperFactory, $mockProgramme, ['opt' => 'foo']),
            $this->factory->programmePresenter($mockProgramme, ['opt' => 'foo'])
        );
    }

    public function testMoleculeCalendar()
    {
        $now = Chronos::now();
        $mockService = $this->createMock(Service::class);

        $this->assertEquals(
            new CalendarPresenter($now, $mockService),
            $this->factory->calendarPresenter($now, $mockService)
        );
    }


    public function testMoleculeDateList()
    {
        $now = Chronos::now();
        $mockService = $this->createMock(Service::class);

        $this->assertEquals(
            new DateListPresenter($this->router, $now, $mockService),
            $this->factory->dateListPresenter($now, $mockService)
        );
    }
}
