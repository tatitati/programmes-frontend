<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Presenters\Domain\CoreEntity\Programme\BroadcastSubPresenters;

use App\Ds2013\Presenters\Domain\CoreEntity\Programme\BroadcastSubPresenters\BroadcastProgrammeTitlePresenter;
use App\DsShared\Helpers\StreamableHelper;
use App\DsShared\Helpers\TitleLogicHelper;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\BroadcastInfoInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BroadcastProgrammeTitlePresenterTest extends TestCase
{
    private $mockRouter;

    private $mockTitleLogicHelper;

    private $streamUrlHelper;

    public function setUp()
    {
        $this->mockRouter = $this->createMock(UrlGeneratorInterface::class);
        $this->mockTitleLogicHelper = $this->createMock(TitleLogicHelper::class);
        $this->streamUrlHelper = $this->createMock(StreamableHelper::class);
    }

    public function testAriaStringIsConstructed()
    {
        $broadcast = $this->createMock(BroadcastInfoInterface::class);
        $broadcast->method('getStartAt')->willReturn(Chronos::create(2017, 25, 25, 6, 15, 0, 'UTC'));
        $programme = $this->createMock(Programme::class);
        $programme->method('getTitle')->willReturn('The programme title');

        $this->mockTitleLogicHelper->method('getOrderedProgrammesForTitle')->willReturn([$programme, []]);

        $presenter = new BroadcastProgrammeTitlePresenter(
            $this->mockRouter,
            $this->mockTitleLogicHelper,
            $broadcast,
            $programme,
            $this->streamUrlHelper,
            ['context_programme' => null]
        );

        $this->assertEquals('25 Jan 06:15: The programme title', $presenter->getAriaTitle());
    }

    public function testAriaStringIsConstructedIntl()
    {
        $timezone = ApplicationTime::getLocalTimeZone()->getName();
        ApplicationTime::setLocalTimeZone('UTC');
        $broadcast = $this->createMock(BroadcastInfoInterface::class);
        $broadcast->method('getStartAt')->willReturn(Chronos::create(2017, 3, 10, 17, 15, 0, 'UTC'));
        $programme = $this->createMock(Programme::class);
        $programme->method('getTitle')->willReturn('The programme title');

        $this->mockTitleLogicHelper->method('getOrderedProgrammesForTitle')->willReturn([$programme, []]);

        $presenter = new BroadcastProgrammeTitlePresenter(
            $this->mockRouter,
            $this->mockTitleLogicHelper,
            $broadcast,
            $programme,
            $this->streamUrlHelper,
            ['context_programme' => null]
        );

        $this->assertEquals('10 Mar 17:15 GMT: The programme title', $presenter->getAriaTitle());
        ApplicationTime::setLocalTimeZone($timezone);
    }
}
