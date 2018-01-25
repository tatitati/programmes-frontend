<?php
declare(strict_types=1);

namespace Tests\App\DsAmen\Presenters\Section\Map\SubPresenter;

use App\DsAmen\Presenters\Section\Map\SubPresenter\TxPresenter;
use App\DsShared\Helpers\LiveBroadcastHelper;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use PHPUnit\Framework\TestCase;
use RMP\Translate\TranslateFactory;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollectionBuilder;

class TxPresenterTest extends TestCase
{
    /** @var LiveBroadcastHelper|\PHPUnit_Framework_MockObject_MockObject */
    private $helper;

    /** @var UrlGenerator */
    private $router;

    /** @var TranslateProvider */
    private $translate;

    public function setup()
    {
        $this->helper = $this->createMock(LiveBroadcastHelper::class);

        $routeCollectionBuilder = new RouteCollectionBuilder();
        $routeCollectionBuilder->add('/programmes/{pid}', '', 'find_by_pid');
        $routeCollectionBuilder->add('/iplayer/episode/{pid}', '', 'iplayer_play');
        $this->router = new UrlGenerator(
            $routeCollectionBuilder->build(),
            new RequestContext()
        );

        $this->translate = new TranslateProvider(new TranslateFactory());
    }

    /** @dataProvider getBadgeTranslationStringProvider */
    public function testGetBadgeTranslationString(
        bool $isRepeat,
        bool $isRadio,
        int $position,
        ?ProgrammeContainer $parent,
        string $expected
    ) {
        $programmeItem = $this->createConfiguredMock(
            ProgrammeItem::class,
            ['getPosition' => $position, 'getParent' => $parent]
        );

        $cb = $this->createConfiguredMock(
            CollapsedBroadcast::class,
            ['isRepeat' => $isRepeat, 'getProgrammeItem' => $programmeItem]
        );

        $context = $this->createConfiguredMock(Brand::class, ['isRadio' => $isRadio]);

        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, $cb, 0, 0);
        $this->assertSame($expected, $tx->getBadgeTranslationString());
    }

    public function getBadgeTranslationStringProvider(): array
    {
        $brand = $this->createConfiguredMock(Brand::class, ['getType' => 'brand', 'isTleo' => true]);
        $series = $this->createConfiguredMock(Series::class, ['getType' => 'series', 'isTleo' => false]);

        return [
            'Repeat returns empty badge' => [true, false, 1, $series, ''],
            'Radio brand returns empty badge' => [false, true, 1, $series, ''],
            'ProgrammeItem belonging to brand returns empty badge' => [false, false, 2, $brand, ''],
            'First position return new series' => [false, false, 1, $series, 'new_series'],
            'Other positions return new' => [false, false, 2, $series, 'new'],
        ];
    }

    /** @dataProvider getTitleTranslationStringProvider */
    public function testGetTitleTranslationString(
        ProgrammeContainer $programmeContainer,
        ?CollapsedBroadcast $collapsedBroadcast,
        bool $isWatchableLive,
        string $expected
    ) {
        $this->helper->method('isWatchableLive')->willReturn($isWatchableLive);
        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $programmeContainer, $collapsedBroadcast, 0, 0);
        $this->assertEquals($expected, $tx->getTitleTranslationString());
    }

    public function getTitleTranslationStringProvider(): array
    {
        $collapsedBroadcast = $this->createMock(CollapsedBroadcast::class);
        $internationalNetwork = $this->createConfiguredMock(Network::class, ['isInternational' => true]);
        $nationalNetwork = $this->createConfiguredMock(Network::class, ['isInternational' => false]);

        $radioBrand = $this->createConfiguredMock(Brand::class, ['isRadio' => true]);
        $internationalTvBrand = $this->createConfiguredMock(Brand::class, ['getNetwork' => $internationalNetwork]);
        $nationalTvBrand = $this->createConfiguredMock(Brand::class, ['getNetwork' => $nationalNetwork]);
        $tvBrandWithoutNetwork = $this->createConfiguredMock(Brand::class, ['getNetwork' => null]);

        return [
            'watchable live radio brand' => [$radioBrand, $collapsedBroadcast, true, 'on_air'],
            'not watchable live radio brand' => [$radioBrand, $collapsedBroadcast, false, 'coming_up'],
            'watchable live national tv brand' => [$nationalTvBrand, $collapsedBroadcast, true, 'on_now'],
            'watchable live international tv brand' => [$internationalTvBrand, $collapsedBroadcast, true, 'on_now'],
            'watchable live tv brand without network' => [$tvBrandWithoutNetwork, $collapsedBroadcast, true, 'on_now'],
            'national tv brand with upcoming broadcast' => [$nationalTvBrand, $collapsedBroadcast, false, 'next_on'],
            'international tv brand with upcoming broadcast' => [$internationalTvBrand, $collapsedBroadcast, false, 'next_on'],
            'tv brand without network with upcoming broadcast' => [$tvBrandWithoutNetwork, $collapsedBroadcast, false, 'next_on'],
            'national tv brand without upcoming broadcast' => [$nationalTvBrand, null, false, 'on_tv'],
            'international tv brand without upcoming broadcast' => [$internationalTvBrand, null, false, 'next_on'],
            'tv brand without network without upcoming broadcast' => [$tvBrandWithoutNetwork, null, false, 'next_on'],
        ];
    }

    public function testLinkTextTranslationString()
    {
        $context = $this->createMock(Brand::class);

        // Upcoming broadcast absent
        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, null, 0, 0);
        $this->assertEquals('all_previous_episodes', $tx->getLinkTextTranslationString());

        // Upcoming broadcast present
        $repeat = $this->createMock(CollapsedBroadcast::class);
        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, $repeat, 0, 0);
        $this->assertEquals('upcoming_episodes', $tx->getLinkTextTranslationString());
    }

    public function testLinkLocationSuffix()
    {
        $context = $this->createMock(Brand::class);

        // Upcoming broadcast absent
        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, null, 0, 0);
        $this->assertEquals('previous', $tx->getLinkLocationSuffix());

        // Upcoming broadcast present
        $repeat = $this->createMock(CollapsedBroadcast::class);
        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, $repeat, 0, 0);
        $this->assertEquals('upcoming', $tx->getLinkLocationSuffix());
    }

    public function testLinkTitleTranslationString()
    {
        $context = $this->createMock(Brand::class);

        // Upcoming broadcast absent
        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, null, 0, 0);
        $this->assertEquals('see_all_episodes_from', $tx->getLinkTitleTranslationString());

        // Upcoming broadcast present
        $repeat = $this->createMock(CollapsedBroadcast::class);
        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, $repeat, 0, 0);
        $this->assertEquals('see_all_upcoming_of', $tx->getLinkTitleTranslationString());
    }

    /** @dataProvider getUpcomingBroadcastCountProvider */
    public function testGetUpcomingBroadcastCount(
        ProgrammeContainer $context,
        int $debutsCount,
        int $repeatsCount,
        string $expected
    ) {
        $upcoming = $this->createMock(CollapsedBroadcast::class);
        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, $upcoming, $debutsCount, $repeatsCount);
        $this->assertSame($expected, $tx->getUpcomingBroadcastCount());
    }

    public function getUpcomingBroadcastCountProvider(): array
    {
        $radioBrand = $this->createConfiguredMock(ProgrammeContainer::class, ['isRadio' => true]);
        $tvBrand = $this->createConfiguredMock(ProgrammeContainer::class, ['isRadio' => false]);

        return [
            'Radio brand page with debut and repeat' => [$radioBrand, 1, 1, '1 new and 1 repeat'],
            'Radio brand page with one debut and no repeats' => [$radioBrand, 1, 0, '1 new'],
            'TV brand page with 2 broadcasts' => [$tvBrand, 1, 1, '2 total'],
            'TV brand page with only 1 repeat' => [$tvBrand, 0, 1, '1 total'],
        ];
    }

    public function testDontShowImageWhenShowingMiniMap()
    {
        $image = $this->createConfiguredMock(Image::class, ['getPid' => new Pid('p058vkxf')]);
        $programme = $this->createConfiguredMock(ProgrammeItem::class, ['getImage' => $image]);
        $cb = $this->createConfiguredMock(CollapsedBroadcast::class, ['getProgrammeItem' => $programme]);

        $context = $this->createMock(Brand::class);

        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, $cb, 0, 0, ['show_mini_map' => true]);
        $this->assertEquals(false, $tx->showImage());
    }

    public function testDontShowImageWhenProgrammeHasSameImageAsContext()
    {
        $image = $this->createConfiguredMock(Image::class, ['getPid' => new Pid('p058vkxf')]);

        $programme = $this->createConfiguredMock(ProgrammeItem::class, ['getImage' => $image]);
        $cb = $this->createConfiguredMock(CollapsedBroadcast::class, ['getProgrammeItem' => $programme]);

        $context = $this->createConfiguredMock(Brand::class, ['getImage' => $image]);

        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, $cb, 0, 0);
        $this->assertEquals(false, $tx->showImage());
    }

    public function testShowImage()
    {
        $image = $this->createConfiguredMock(Image::class, ['getPid' => new Pid('p058vkxf')]);
        $programme = $this->createConfiguredMock(ProgrammeItem::class, ['getImage' => $image]);
        $cb = $this->createConfiguredMock(CollapsedBroadcast::class, ['getProgrammeItem' => $programme]);

        $image2 = $this->createConfiguredMock(Image::class, ['getPid' => new Pid('p0000000')]);
        $context = $this->createConfiguredMock(Brand::class, ['getImage' => $image2]);

        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, $cb, 0, 0);
        $this->assertEquals(true, $tx->showImage());
    }
}
