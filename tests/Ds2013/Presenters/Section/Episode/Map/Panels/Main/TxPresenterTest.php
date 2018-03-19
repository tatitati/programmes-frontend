<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Presenters\Section\Episode\Map\Panels\Main;

use App\Builders\CollapsedBroadcastBuilder;
use App\Builders\EpisodeBuilder;
use App\Ds2013\Presenters\Section\Episode\Map\Panels\Side\TxPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @group MapEpisode
 */
class TxPresenterTest extends TestCase
{
    public function testTxpanelPreferToWorkWithUpcomingOverLastOn()
    {
        $upcoming = CollapsedBroadcastBuilder::any()->build();
        $lastOn = CollapsedBroadcastBuilder::any()->build();

        $tx = new TxPresenter($upcoming, $lastOn);

        $this->assertEquals(
            $upcoming,
            $tx->getCollapsedBroadcast()
        );
    }

    public function testTxPanelWorkWithlastOnIfUpcomingDontExist()
    {
        $lastOn = CollapsedBroadcastBuilder::any()->build();

        $tx = new TxPresenter(null, $lastOn);

        $this->assertEquals(
            $lastOn,
            $tx->getCollapsedBroadcast()
        );
    }

    /**
     * [ Title ]. They are based on when is being broadcasted and the type of programmeType
     *
     * @dataProvider outputsBasedOnEpisodeTypeForBroadcastsOnNow
     */
    public function testOnNowForTypeEpisode(CollapsedBroadcastBuilder $broadcastOnTimeBuilder, Episode $withEpisode, string $expectedPanelTitle)
    {
        $broadcastOnTime = $broadcastOnTimeBuilder->with(['programmeItem' => $withEpisode])->build();

        $presenter = new TxPresenter($broadcastOnTime, null);

        $this->assertEquals($expectedPanelTitle, $presenter->getTitle());
    }

    public function outputsBasedOnEpisodeTypeForBroadcastsOnNow()
    {
        $radioEpisode = EpisodeBuilder::anyRadioEpisode()->build();
        $tvEpisode = EpisodeBuilder::anyTVEpisode()->build();

        $broadcastonPast = CollapsedBroadcastBuilder::anyOnPast();
        $broadcastLive = CollapsedBroadcastBuilder::anyLive();
        $braodcastOnFuture = CollapsedBroadcastBuilder::anyOnFuture();

        return [
            [$broadcastonPast, $tvEpisode, 'last_on', ],
            [$broadcastonPast, $radioEpisode, 'last_on', ],

            [$broadcastLive, $tvEpisode, 'on_now'],
            [$broadcastLive, $radioEpisode, 'on_air'],

            [$braodcastOnFuture, $tvEpisode, 'on_tv'],
            [$braodcastOnFuture, $radioEpisode, 'on_radio'],
        ];
    }

    /**
     * [ Edge case ]. TX panel is created by MapEpisode, but for an unproper use we might not pass any broadcast
     */
    public function testItThrowsAnException()
    {
        $this->expectException(InvalidArgumentException::class);

        new TxPresenter(null, null);
    }
}
