<?php
declare(strict_types=1);

namespace Tests\App\DsAmen\Organism\Programme\CollapsedBroadcastSubPresenters;

use App\DsAmen\Organism\Programme\CollapsedBroadcastSubPresenters\CollapsedBroadcastProgrammeCtaPresenter;
use App\DsShared\Helpers\LiveBroadcastHelper;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\App\DsAmen\Organism\Programme\BaseProgrammeSubPresenterTest;

class CollapsedBroadcastProgrammeCtaPresenterTest extends BaseProgrammeSubPresenterTest
{
    /** @var UrlGeneratorInterface */
    private $router;

    /** @var LiveBroadcastHelper */
    private $liveBroadcastHelper;

    protected function setUp()
    {
        $this->router = $this->createRouter();
        $this->liveBroadcastHelper = new LiveBroadcastHelper($this->router);
    }

    /** @dataProvider getMediaIconNameProvider */
    public function testGetMediaIconName(CollapsedBroadcast $collapsedBroadcast, array $options, string $expected): void
    {
        $ctaPresenter = new CollapsedBroadcastProgrammeCtaPresenter(
            $collapsedBroadcast,
            $this->router,
            $this->liveBroadcastHelper,
            $options
        );

        $this->assertSame($expected, $ctaPresenter->getMediaIconName());
    }

    public function getMediaIconNameProvider(): array
    {
        $tvEpisode = $this->createMockTvEpisode();
        $clip = $this->createMockClip();
        $radioEpisode = $this->createMockRadioEpisode();

        $cb1 = $this->createMockCollapsedBroadcast($tvEpisode);
        $cb2 = $this->createMockCollapsedBroadcast($clip);
        $cb3 = $this->createMockCollapsedBroadcast($radioEpisode);

        return [
            'TV episode shows iPlayer CTA icon' => [$cb1, [], 'iplayer'],
            'Clip shows play CTA icon' => [$cb2, [], 'play'],
            'Radio episode shows iPlayer Radio CTA icon' => [$cb3, [], 'iplayer-radio'],
            'link_to_start option shows rewind button' => [$cb3, ['link_to_start' => true], 'live-restart'],
        ];
    }

    /** @dataProvider getLabelTranslationProvider */
    public function testGetLabelTranslation(CollapsedBroadcast $collapsedBroadcast, array $options, string $expected): void
    {
        $ctaPresenter = new CollapsedBroadcastProgrammeCtaPresenter(
            $collapsedBroadcast,
            $this->router,
            $this->liveBroadcastHelper,
            $options
        );

        $this->assertSame($expected, $ctaPresenter->getLabelTranslation());
    }

    public function getLabelTranslationProvider(): array
    {
        $tvEpisode = $this->createMockTvEpisode();
        $clip = $this->createMockClip();
        $radioEpisode = $this->createMockRadioEpisode();

        $cb1 = $this->createMockCollapsedBroadcast($tvEpisode);
        $cb2 = $this->createMockCollapsedBroadcast($clip);
        $cb3 = $this->createMockCollapsedBroadcast($radioEpisode);

        return [
            'Non audio TV episode shows watch now string' => [$cb1, [], 'iplayer_watch_live'],
            'Non audio Clip shows shows watch now string' => [$cb2, [], 'iplayer_watch_live'],
            'Audio only Radio episode shows listen now string' => [$cb3, [], 'iplayer_listen_live'],
            'link_to_start option shows watch from start string' => [$cb3, ['link_to_start' => true], 'iplayer_watch_from_start'],
        ];
    }

    /** @dataProvider getBackgroundClassProvider */
    public function testGetBackgroundClass(array $options, string $expected): void
    {
        $collapsedBroadcast = $this->createMockCollapsedBroadcast();

        $ctaPresenter = new CollapsedBroadcastProgrammeCtaPresenter(
            $collapsedBroadcast,
            $this->router,
            $this->liveBroadcastHelper,
            $options
        );

        $this->assertSame($expected, $ctaPresenter->getBackgroundClass());
    }

    public function getBackgroundClassProvider(): array
    {
        return [
            'show_image option means no background class' => [['show_image' => true], ''],
            'no show_image option means background class' => [['show_image' => false], 'icon--remove-background'],
        ];
    }

    private function createMockCollapsedBroadcast(ProgrammeItem $programmeItem = null)
    {
        if (!$programmeItem) {
            $programmeItem = $this->createMock(ProgrammeItem::class);
        }

        $cb = $this->createMock(CollapsedBroadcast::class);
        $cb->method('getProgrammeItem')->willReturn($programmeItem);

        return $cb;
    }
}
