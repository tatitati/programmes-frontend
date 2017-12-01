<?php
declare(strict_types=1);

namespace Tests\App\DsAmen\Presenters\Domain\CoreEntity\CollapsedBroadcast;

use App\DsAmen\Presenters\Domain\CoreEntity\CollapsedBroadcast\CollapsedBroadcastPresenter;
use App\DsAmen\Presenters\Domain\CoreEntity\CollapsedBroadcast\SubPresenter\CtaPresenter;
use App\DsAmen\Presenters\Domain\CoreEntity\CollapsedBroadcast\SubPresenter\DetailsPresenter;
use App\DsAmen\Presenters\Domain\CoreEntity\CollapsedBroadcast\SubPresenter\LiveCtaPresenter;
use App\DsAmen\Presenters\Domain\CoreEntity\Shared\SubPresenter\BodyPresenter;
use App\DsAmen\Presenters\Domain\CoreEntity\Shared\SubPresenter\ImagePresenter;
use App\DsAmen\Presenters\Domain\CoreEntity\Shared\SubPresenter\StreamableCtaPresenter;
use App\DsAmen\Presenters\Domain\CoreEntity\Shared\SubPresenter\TitlePresenter;
use App\DsShared\Helpers\BroadcastNetworksHelper;
use App\DsShared\Helpers\HelperFactory;
use App\DsShared\Helpers\LiveBroadcastHelper;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use RMP\Translate\TranslateFactory;
use Symfony\Component\Routing\Generator\UrlGenerator;

class CollapsedBroadcastPresenterTest extends TestCase
{
    /** @var UrlGenerator|PHPUnit_Framework_MockObject_MockObject */
    private $mockRouter;

    /** @var TranslateProvider */
    private $translate;

    /** @var HelperFactory|PHPUnit_Framework_MockObject_MockObject */
    private $mockHelperFactory;

    /** @var CollapsedBroadcast|PHPUnit_Framework_MockObject_MockObject */
    private $mockCollapsedBroadcast;

    /** @var ProgrammeItem|PHPUnit_Framework_MockObject_MockObject */
    private $mockProgrammeItem;

    public function setUp()
    {
        $this->mockRouter = $this->createMock(UrlGenerator::class);
        $this->translate = new TranslateProvider(new TranslateFactory());
        $this->mockCollapsedBroadcast = $this->createMockCollapsedBroadcast();

        $this->mockHelperFactory  = $this->createMock(HelperFactory::class);
        $this->mockHelperFactory
            ->method('getBroadcastNetworksHelper')
            ->willReturn($this->createMock(BroadcastNetworksHelper::class));
    }

    public function testGetBodyPresenterReturnsInstanceOfSharedBodyPresenter(): void
    {
        $cbPresenter = new CollapsedBroadcastPresenter(
            $this->mockCollapsedBroadcast,
            $this->mockRouter,
            $this->translate,
            $this->mockHelperFactory
        );
        $this->assertInstanceOf(BodyPresenter::class, $cbPresenter->getBodyPresenter());
    }

    public function testGetCtaPresenterReturnsStreamableWhenStreamable(): void
    {
        $this->setStreamableAndLiveExpectations(true, false);

        $cbPresenter = new CollapsedBroadcastPresenter(
            $this->mockCollapsedBroadcast,
            $this->mockRouter,
            $this->translate,
            $this->mockHelperFactory
        );
        $this->assertInstanceOf(StreamableCtaPresenter::class, $cbPresenter->getCtaPresenter());
    }

    public function testGetCtaPresenterReturnsLiveWhenLive(): void
    {
        $this->setStreamableAndLiveExpectations(false, true);

        $cbPresenter = new CollapsedBroadcastPresenter(
            $this->mockCollapsedBroadcast,
            $this->mockRouter,
            $this->translate,
            $this->mockHelperFactory
        );
        $this->assertInstanceOf(LiveCtaPresenter::class, $cbPresenter->getCtaPresenter());
    }

    public function testGetCtaPresenterReturnsNullWhenNotStreamableOrLive(): void
    {
        $this->setStreamableAndLiveExpectations(false, false);

        $cbPresenter = new CollapsedBroadcastPresenter(
            $this->mockCollapsedBroadcast,
            $this->mockRouter,
            $this->translate,
            $this->mockHelperFactory
        );
        $this->assertNull($cbPresenter->getCtaPresenter());
    }

    public function testGetDetailsPresenterReturnsInstanceOfCollapsedBroadcastDetailsPresenter(): void
    {
        $cbPresenter = new CollapsedBroadcastPresenter(
            $this->mockCollapsedBroadcast,
            $this->mockRouter,
            $this->translate,
            $this->mockHelperFactory
        );
        $this->assertInstanceOf(DetailsPresenter::class, $cbPresenter->getDetailsPresenter());
    }

    public function testGetImagePresenterReturnsInstanceOfSharedImagePresenter(): void
    {
        $cbPresenter = new CollapsedBroadcastPresenter(
            $this->mockCollapsedBroadcast,
            $this->mockRouter,
            $this->translate,
            $this->mockHelperFactory
        );
        $this->assertInstanceOf(ImagePresenter::class, $cbPresenter->getImagePresenter());
    }

    public function testGetTitlePresenterReturnsInstanceOfSharedTitlePresenter(): void
    {
        $cbPresenter = new CollapsedBroadcastPresenter(
            $this->mockCollapsedBroadcast,
            $this->mockRouter,
            $this->translate,
            $this->mockHelperFactory
        );
        $this->assertInstanceOf(TitlePresenter::class, $cbPresenter->getTitlePresenter());
    }

    /** @dataProvider showStandaloneCtaProvider */
    public function testShowStandaloneCta(bool $isOnAir, bool $isStreamable, array $options, bool $expected): void
    {
        $this->setStreamableAndLiveExpectations($isStreamable, $isOnAir);

        $cbPresenter = new CollapsedBroadcastPresenter(
            $this->mockCollapsedBroadcast,
            $this->mockRouter,
            $this->translate,
            $this->mockHelperFactory,
            $options
        );

        $this->assertEquals($expected, $cbPresenter->showStandaloneCta());
    }

    public function showStandaloneCtaProvider(): array
    {
        return [
            'Show Standalone CTA when on air and not showing image' => [true, false, ['show_image' => false], true],
            'Show Standalone CTA when streamable and not showing image' => [false, true, ['show_image' => false], true],
            'Show Standalone CTA when, streamable, on air and not showing image' => [true, true, ['show_image' => false], true],
            'Do not show Standalone CTA when showing image' => [true, true, ['show_image' => true], false],
        ];
    }

    /** @dataProvider showWatchFromStartCtaProvider */
    public function testShowWatchFromStartCta(bool $isOnAir, bool $isRadio, bool $expected): void
    {
        $this->setStreamableAndLiveExpectations(false, $isOnAir);

        $this->mockProgrammeItem->expects($this->atLeastOnce())
            ->method('isRadio')
            ->willReturn($isRadio);

        $cbPresenter = new CollapsedBroadcastPresenter(
            $this->mockCollapsedBroadcast,
            $this->mockRouter,
            $this->translate,
            $this->mockHelperFactory
        );

        $this->assertEquals($expected, $cbPresenter->showWatchFromStartCta());
    }

    public function showWatchFromStartCtaProvider(): array
    {
        return [
            'Show Watch from start CTA when on air and not Radio' => [true, false, true],
            'Do not show Watch from start CTA when not on air' => [false, false, false],
            'Do not show Watch from start CTA when programme is from radio' => [false, true, false],
        ];
    }

    private function createMockCollapsedBroadcast()
    {
        $cb = $this->createMock(CollapsedBroadcast::class);
        return $cb;
    }

    private function setStreamableAndLiveExpectations(bool $isStreamable, bool $isLive)
    {
        $this->mockProgrammeItem = $this->createMock(ProgrammeItem::class);

        $this->mockProgrammeItem->expects($this->any())
            ->method('isStreamable')
            ->willReturn($isStreamable);

        $this->mockCollapsedBroadcast->expects($this->atLeastOnce())
            ->method('getProgrammeItem')
            ->willReturn($this->mockProgrammeItem);

        $mockLiveBroadcastHelper = $this->createMock(LiveBroadcastHelper::class);
        $mockLiveBroadcastHelper->expects($this->any())
            ->method('isWatchableLive')
            ->with($this->mockCollapsedBroadcast, false)
            ->willReturn($isLive);

        $this->mockHelperFactory->expects($this->any())
            ->method('getLiveBroadcastHelper')
            ->willReturn($mockLiveBroadcastHelper);
    }
}
