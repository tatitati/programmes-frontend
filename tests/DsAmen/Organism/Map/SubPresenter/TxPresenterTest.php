<?php
declare(strict_types=1);

namespace Tests\App\DsAmen\Organism\Map\SubPresenter;

use App\DsAmen\Organism\Map\SubPresenter\TxPresenter;
use App\DsShared\Helpers\LiveBroadcastHelper;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
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
    public function testGetBadgeTranslationStringReturnsEmptyForRepeatBroadcast(bool $isRepeat, bool $isRadio, int $position, string $expected)
    {
        $programmeItem = $this->createConfiguredMock(ProgrammeItem::class, ['getPosition' => $position]);

        $cb = $this->createConfiguredMock(
            CollapsedBroadcast::class,
            ['isRepeat' => $isRepeat, 'getProgrammeItem' => $programmeItem]
        );

        $context = $this->createConfiguredMock(Brand::class, ['isRadio' => $isRadio]);

        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, [$cb]);
        $this->assertSame($expected, $tx->getBadgeTranslationString());
    }

    public function getBadgeTranslationStringProvider(): array
    {
        return [
            'Repeat return empty badge' => [true, false, 1, ''],
            'Radio brand returns empty badge' => [false, true, 1, ''],
            'First position return new series' => [false, false, 1, 'new_series'],
            'Other positions return new' => [false, false, 2, 'new'],
        ];
    }

    public function testLinkTextTranslationString()
    {
        $context = $this->createMock(Brand::class);

        // Upcoming broadcast present
        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, []);
        $this->assertEquals('all_previous_episodes', $tx->getLinkTextTranslationString());

        // Upcoming broadcast absent
        $repeat = $this->createMock(CollapsedBroadcast::class);
        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, [$repeat]);
        $this->assertEquals('upcoming_episodes', $tx->getLinkTextTranslationString());
    }

    public function testLinkTitleTranslationString()
    {
        $context = $this->createMock(Brand::class);

        // Upcoming broadcast present
        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, []);
        $this->assertEquals('see_all_episodes_from', $tx->getLinkTitleTranslationString());

        // Upcoming broadcast absent
        $repeat = $this->createMock(CollapsedBroadcast::class);
        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, [$repeat]);
        $this->assertEquals('see_all_upcoming_of', $tx->getLinkTitleTranslationString());
    }

    public function testTxPresenterFavorsDisplayingDebuts()
    {
        $debutProgrammeItem = $this->createConfiguredMock(ProgrammeItem::class, ['getPid' => new Pid('dbtprgrmpd')]);

        $debut = $this->createConfiguredMock(
            CollapsedBroadcast::class,
            ['getProgrammeItem' => $debutProgrammeItem, 'isRepeat' => false]
        );

        $repeatProgrammeItem = $this->createConfiguredMock(ProgrammeItem::class, ['getPid' => new Pid('rptprgrmpd')]);

        $repeat = $this->createConfiguredMock(
            CollapsedBroadcast::class,
            ['getProgrammeItem' => $repeatProgrammeItem, 'isRepeat' => true]
        );

        $context = $this->createMock(Brand::class);

        // Repeat is the first one in the upcoming broadcast array
        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, [$repeat, $debut]);
        $this->assertEquals('dbtprgrmpd', (string) $tx->getCollapsedBroadcast()->getProgrammeItem()->getPid());
    }

    public function testTxPresenterUsesRepeatWhenThereAreNoDebuts()
    {
        $repeatProgrammeItem = $this->createConfiguredMock(ProgrammeItem::class, ['getPid' => new Pid('rptprgrmpd')]);

        $repeat = $this->createConfiguredMock(
            CollapsedBroadcast::class,
            ['getProgrammeItem' => $repeatProgrammeItem, 'isRepeat' => true]
        );

        $context = $this->createMock(Brand::class);

        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, [$repeat]);
        $this->assertEquals('rptprgrmpd', (string) $tx->getCollapsedBroadcast()->getProgrammeItem()->getPid());
    }

    /** @dataProvider getUpcomingBroadcastCountProvider */
    public function testGetUpcomingBroadcastCount(ProgrammeContainer $context, array $upcoming, string $expected)
    {
        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, $upcoming);
        $this->assertSame($expected, $tx->getUpcomingBroadcastCount());
    }

    public function getUpcomingBroadcastCountProvider(): array
    {
        $radioBrand = $this->createConfiguredMock(ProgrammeContainer::class, ['isRadio' => true]);
        $tvBrand = $this->createConfiguredMock(ProgrammeContainer::class, ['isRadio' => false]);
        $repeat = $this->createConfiguredMock(CollapsedBroadcast::class, ['isRepeat' => true]);
        $debut = $this->createConfiguredMock(CollapsedBroadcast::class, ['isRepeat' => false]);

        return [
            'Radio brand page with debut and repeat' => [$radioBrand, [$debut, $repeat], '1 new and 1 repeat'],
            'Radio brand page without debut and repeat' => [$radioBrand, [$debut], '1 new'],
            'TV brand page with 2 broadcasts' => [$tvBrand, [$debut, $repeat], '2 total'],
            'TV brand page with only 1 repeat' => [$tvBrand, [$repeat], '1 total'],
        ];
    }

    public function testDontShowImageWhenShowingMiniMap()
    {
        $image = $this->createConfiguredMock(Image::class, ['getPid' => new Pid('p058vkxf')]);
        $programme = $this->createConfiguredMock(ProgrammeItem::class, ['getImage' => $image]);
        $cb = $this->createConfiguredMock(CollapsedBroadcast::class, ['getProgrammeItem' => $programme]);

        $context = $this->createMock(Brand::class);

        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, [$cb], ['show_mini_map' => true]);
        $this->assertEquals(false, $tx->showImage());
    }

    public function testDontShowImageWhenProgrammeHasSameImageAsContext()
    {
        $image = $this->createConfiguredMock(Image::class, ['getPid' => new Pid('p058vkxf')]);

        $programme = $this->createConfiguredMock(ProgrammeItem::class, ['getImage' => $image]);
        $cb = $this->createConfiguredMock(CollapsedBroadcast::class, ['getProgrammeItem' => $programme]);

        $context = $this->createConfiguredMock(Brand::class, ['getImage' => $image]);

        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, [$cb]);
        $this->assertEquals(false, $tx->showImage());
    }

    public function testShowImage()
    {
        $image = $this->createConfiguredMock(Image::class, ['getPid' => new Pid('p058vkxf')]);
        $programme = $this->createConfiguredMock(ProgrammeItem::class, ['getImage' => $image]);
        $cb = $this->createConfiguredMock(CollapsedBroadcast::class, ['getProgrammeItem' => $programme]);

        $image2 = $this->createConfiguredMock(Image::class, ['getPid' => new Pid('p0000000')]);
        $context = $this->createConfiguredMock(Brand::class, ['getImage' => $image2]);

        $tx = new TxPresenter($this->helper, $this->translate, $this->router, $context, [$cb]);
        $this->assertEquals(true, $tx->showImage());
    }
}
