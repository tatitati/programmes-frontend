<?php
declare(strict_types = 1);
namespace Tests\App\Ds2013;

use App\Ds2013\PresenterFactory;
use App\Ds2013\Presenters\Domain\Broadcast\BroadcastPresenter;
use App\Ds2013\Presenters\Domain\Programme\ProgrammePresenter;
use App\Ds2013\Presenters\Utilities\Calendar\CalendarPresenter;
use App\Ds2013\Presenters\Utilities\DateList\DateListPresenter;
use App\DsShared\Helpers\HelperFactory;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Chronos;
use Cake\Chronos\Date;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use RMP\Translate\Translate;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @covers \App\Ds2013\PresenterFactory
 */
class PresenterFactoryTest extends TestCase
{
    /** @var Translate|PHPUnit_Framework_MockObject_MockObject. */
    private $translate;

    /** @var UrlGeneratorInterface|PHPUnit_Framework_MockObject_MockObject */
    private $router;

    /** @var HelperFactory|PHPUnit_Framework_MockObject_MockObject */
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

    public function tearDown()
    {
        Chronos::setTestNow();
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
        $now = Date::now();
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

        Chronos::setTestNow($now);

        $this->assertEquals(
            new DateListPresenter($this->router, $now, $mockService),
            $this->factory->dateListPresenter($now, $mockService)
        );
    }
}
