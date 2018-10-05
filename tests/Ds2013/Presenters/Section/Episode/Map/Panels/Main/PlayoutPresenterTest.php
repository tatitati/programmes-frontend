<?php
declare (strict_types = 1);

namespace Tests\App\Ds2013\Presenters\Section\Episode\Map\Panels\Main;

use App\Builders\EpisodeBuilder;
use App\Ds2013\Presenters\Section\Episode\Map\Panels\Main\PlayoutPresenter;
use App\DsShared\Helpers\LiveBroadcastHelper;
use App\DsShared\Helpers\StreamableHelper;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\VersionType;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollectionBuilder;

class PlayoutPresenterTest extends TestCase
{
    private $router;

    private $liveBroadcastHelper;

    /** @var StreamableHelper|\PHPUnit_Framework_MockObject_MockObject */
    private $streamableHelper;

    public function setUp()
    {
        $routeCollectionBuilder = new RouteCollectionBuilder();
        $routeCollectionBuilder->add('/playspace/{pid}', '', 'playspace_play');
        $routeCollectionBuilder->add('/iplayer/{pid}', '', 'iplayer_play');
        $this->router = new UrlGenerator($routeCollectionBuilder->build(), new RequestContext());
        $this->liveBroadcastHelper = $this->createMock(LiveBroadcastHelper::class);
        $this->streamableHelper = $this->createMock(StreamableHelper::class);
    }

    /** @dataProvider getIconProvider */
    public function testGetIcon(bool $isAudio, string $expected)
    {
        $episode = EpisodeBuilder::any()->build();
        $this->streamableHelper->method('shouldTreatProgrammeItemAsAudio')->willReturn($isAudio);
        $presenter = new PlayoutPresenter($this->liveBroadcastHelper, $this->streamableHelper, $this->router, $episode, null, null);
        $this->assertEquals($expected, $presenter->getIcon());
    }

    public function getIconProvider(): array
    {
        return [
            'radio episode returns radio icon' => [true, 'listen'],
            'non-radio episode return iplayer icon' => [false, 'iplayer'],
        ];
    }

    /** @dataProvider getNotAvailableTranslationProvider */
    public function testGetNotAvailableTranslation(
        string $expected,
        bool $hasFutureAvailability,
        bool $isRadio,
        bool $isTleo,
        Chronos $startAt
    ) {
        $episode = $this->createConfiguredMock(
            Episode::class,
            ['hasFutureAvailability' => $hasFutureAvailability, 'isRadio' => $isRadio, 'isTleo' => $isTleo]
        );

        $collapsedBroadcast = $this->createConfiguredMock(CollapsedBroadcast::class, ['getStartAt' => $startAt]);

        $presenter = new PlayoutPresenter($this->liveBroadcastHelper, $this->streamableHelper, $this->router, $episode, $collapsedBroadcast, null);
        $this->assertEquals($expected, $presenter->getNotAvailableTranslation());
    }

    public function getNotAvailableTranslationProvider()
    {
        $future = $this->createConfiguredMock(Chronos::class, ['isFuture' => true]);
        $past = $this->createConfiguredMock(Chronos::class, ['isFuture' => false]);

        return [
            'Broadcast in the future and episode available in the future shows availability_shortly' => [
                'available_shortly',
                true,
                false,
                false,
                $future,
            ],
            'Broadcast in the future and episode not available in the future but is from radio shows availability_shortly' => [
                'available_shortly',
                false,
                true,
                false,
                $future,
            ],
            'Broadcast in the past but episode has availability in the future shows episode_availability_future' => [
                'episode_availability_future',
                true,
                false,
                false,
                $past,
            ],
            'No future availability, non-tleo radio episode shows episode_availability_none_radio' => [
                'episode_availability_none_radio',
                false,
                true,
                false,
                $past,
            ],
            'No future availability tleo radio episode shows programme_availability_none_radio' => [
                'programme_availability_none_radio',
                false,
                true,
                true,
                $past,
            ],
            'No future availability, non-tleo non-radio episode shows episode_availability_none' => [
                'episode_availability_none',
                false,
                false,
                false,
                $past,
            ],
            'No future availability tleo non-radio episode shows programme_availability_none' => [
                'programme_availability_none',
                false,
                false,
                true,
                $past,
            ],
        ];
    }

    /** @dataProvider isAvailableForStreamingProvider */
    public function testIsAvailableForStreaming(
        bool $expected,
        bool $hasPlayableDestination,
        bool $isWatchableLive,
        ?CollapsedBroadcast $collapsedBroadcast
    ) {
        $episode = $this->createConfiguredMock(Episode::class, ['hasPlayableDestination' => $hasPlayableDestination]);
        $this->liveBroadcastHelper->method('isWatchableLive')->willReturn($isWatchableLive);

        $presenter = new PlayoutPresenter($this->liveBroadcastHelper, $this->streamableHelper, $this->router, $episode, $collapsedBroadcast, null);
        $this->assertEquals($expected, $presenter->isAvailableForStreaming());
    }

    public function isAvailableForStreamingProvider(): array
    {
        $collapsedBroadcast = $this->createMock(CollapsedBroadcast::class);

        return [
            'Episode is playable returns true' => [true, true, false, null],
            'Not playable but is watchable live returns true' => [true, false, true, $collapsedBroadcast],
            'Not playable and not watchable live returns false' => [false, false, false, $collapsedBroadcast],
            'Not playable and no broadcast returns false' => [false, false, false, null],
        ];
    }

    /** @dataProvider getAvailableTranslationProvider */
    public function testGetAvailableTranslation(string $expected, bool $isWatchableLive, bool $isAudio)
    {
        $episode = $this->createConfiguredMock(Episode::class, ['isAudio' => $isAudio]);
        $this->liveBroadcastHelper->method('isWatchableLive')->willReturn($isWatchableLive);
        $collapsedBroadcast = $this->createMock(CollapsedBroadcast::class);
        $this->streamableHelper->method('shouldTreatProgrammeItemAsAudio')->willReturn($isAudio);
        $presenter = new PlayoutPresenter($this->liveBroadcastHelper, $this->streamableHelper, $this->router, $episode, $collapsedBroadcast, null);
        $this->assertEquals($expected, $presenter->getAvailableTranslation());
    }

    public function getAvailableTranslationProvider()
    {
        return [
            'on air audio episode returns iplayer_listen_live' => ['iplayer_listen_live', true, true],
            'on air non-audio episode returns iplayer_watch_live' => ['iplayer_watch_live', true, false],
            'not on air audio episode returns iplayer_listen_now' => ['iplayer_listen_now', false, true],
            'not on air non-audio episode returns iplayer_watch_now' => ['iplayer_watch_now', false, false],
        ];
    }

    /** @dataProvider getUrlProvider */
    public function testGetUrl(string $expected, ?CollapsedBroadcast $cb, bool $isWatchableLive, string $route)
    {
        $this->liveBroadcastHelper->method('simulcastUrl')->willReturn('simulcastUrl');
        $this->liveBroadcastHelper->method('isWatchableLive')->willReturn($isWatchableLive);
        $episode = $this->createConfiguredMock(Episode::class, ['getPid' => new Pid('b0000001')]);
        $this->streamableHelper->method('getRouteForProgrammeItem')->willReturn($route);

        $presenter = new PlayoutPresenter($this->liveBroadcastHelper, $this->streamableHelper, $this->router, $episode, $cb, null);

        $this->assertEquals($expected, $presenter->getUrl());
    }

    public function getUrlProvider(): array
    {
        $cb = $this->createMock(CollapsedBroadcast::class);

        return [
            'is watchable live returns simulcasturl' => ['simulcastUrl', $cb, true, ''],
            'not watchable live radio episode returns playspace url' => ['/playspace/b0000001', $cb, false, 'playspace_play'],
            'no collapsed broadcast so radio episode returns playspace url' => ['/playspace/b0000001', null, true, 'playspace_play'],
            'not watchable live non-radio episode returns iplayer url' => ['/iplayer/b0000001', $cb, false, 'iplayer_play'],
            'no collapsed broadcast so non-radio episode returns iplayer url' => ['/iplayer/b0000001', null, true, 'iplayer_play'],
        ];
    }

    /** @dataProvider doesntHaveOverlayProvider */
    public function testDoesntHaveOverlay(
        bool $expected,
        bool $isStreamableAlternate,
        bool $isDownloadable,
        bool $isInternational,
        bool $hasPlayableDestination,
        bool $isWatchableLive
    ) {
        $cb = $this->createMock(CollapsedBroadcast::class);
        $network = $this->createConfiguredMock(Network::class, ['isInternational' => $isInternational]);

        $episode = $this->createConfiguredMock(
            Episode::class,
            ['getNetwork' => $network, 'isDownloadable' => $isDownloadable, 'hasPlayableDestination' => $hasPlayableDestination, 'isStreamableAlternate' => $isStreamableAlternate]
        );

        $this->liveBroadcastHelper->method('isWatchableLive')->willReturn($isWatchableLive);

        $presenter = new PlayoutPresenter($this->liveBroadcastHelper, $this->streamableHelper, $this->router, $episode, $cb, null);
        $this->assertEquals($expected, $presenter->doesntHaveOverlay());
    }

    public function doesntHaveOverlayProvider(): array
    {
        return [
            // streamable cases, have overlay
            'is available on demand, so has overlay' =>
                [false, true, true, false, true, false],
            'is live, so has overlay' =>
                [false, true, true, false, false, true],

            // not streamable cases, no overlay
            'has audio described version, so no overlay' =>
                [true, true, false, false, false, false],
            'has signed version, so no overlay' =>
                [true, true, false, false, false, false],
            'is downloadable, so no overlay' =>
                [true, false, true, false, false, false],
        ];
    }
}
