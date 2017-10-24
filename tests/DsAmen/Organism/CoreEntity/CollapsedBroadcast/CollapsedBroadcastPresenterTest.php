<?php
declare(strict_types=1);

namespace Tests\App\DsAmen\Organism\CoreEntity\CollapsedBroadcast;

use App\DsAmen\Organism\CoreEntity\CollapsedBroadcast\CollapsedBroadcastPresenter;
use App\DsAmen\Organism\CoreEntity\CollapsedBroadcast\SubPresenter\CtaPresenter;
use App\DsAmen\Organism\CoreEntity\CollapsedBroadcast\SubPresenter\DetailsPresenter;
use App\DsAmen\Organism\CoreEntity\Shared\SubPresenter\SharedBodyPresenter;
use App\DsAmen\Organism\CoreEntity\Shared\SubPresenter\SharedImagePresenter;
use App\DsAmen\Organism\CoreEntity\Shared\SubPresenter\SharedTitlePresenter;
use App\DsShared\Helpers\BroadcastNetworksHelper;
use App\DsShared\Helpers\HelperFactory;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
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
        $this->assertInstanceOf(SharedBodyPresenter::class, $cbPresenter->getBodyPresenter());
    }

    public function testGetCtaPresenterReturnsInstanceOfCollapsedBroadcastCtaPresenter(): void
    {
        $cbPresenter = new CollapsedBroadcastPresenter(
            $this->mockCollapsedBroadcast,
            $this->mockRouter,
            $this->translate,
            $this->mockHelperFactory
        );
        $this->assertInstanceOf(CtaPresenter::class, $cbPresenter->getCtaPresenter());
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
        $this->assertInstanceOf(SharedImagePresenter::class, $cbPresenter->getImagePresenter());
    }

    public function testGetTitlePresenterReturnsInstanceOfSharedTitlePresenter(): void
    {
        $cbPresenter = new CollapsedBroadcastPresenter(
            $this->mockCollapsedBroadcast,
            $this->mockRouter,
            $this->translate,
            $this->mockHelperFactory
        );
        $this->assertInstanceOf(SharedTitlePresenter::class, $cbPresenter->getTitlePresenter());
    }

    /** @dataProvider showStandaloneCtaProvider */
    public function testShowStandaloneCta(bool $isOnAir, bool $isStreamable, array $options, bool $expected): void
    {
        $cb = clone $this->mockCollapsedBroadcast;
        $cb->method('isOnAir')->willReturn($isOnAir);

        $episode = $this->createMock(Episode::class);
        $episode->method('isStreamable')->willReturn($isStreamable);

        $cb->method('getProgrammeItem')->willReturn($episode);

        $cbPresenter = new CollapsedBroadcastPresenter(
            $cb,
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
        $cb = clone $this->mockCollapsedBroadcast;
        $cb->method('isOnAir')->willReturn($isOnAir);

        $episode = $this->createMock(Episode::class);
        $episode->method('isRadio')->willReturn($isRadio);

        $cb->method('getProgrammeItem')->willReturn($episode);

        $cbPresenter = new CollapsedBroadcastPresenter(
            $cb,
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
}
