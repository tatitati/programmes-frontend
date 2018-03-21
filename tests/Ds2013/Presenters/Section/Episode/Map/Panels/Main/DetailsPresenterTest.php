<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Presenters\Section\Episode\Map\Panels\Main;

use App\Builders\EpisodeBuilder;
use App\Ds2013\Presenters\Section\Episode\Map\Panels\Main\DetailsPresenter;
use App\DsShared\Helpers\PlayTranslationsHelper;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\Entity\VersionType;
use BBC\ProgrammesPagesService\Domain\ValueObject\PartialDate;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Cake\Chronos\Chronos;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @group MapEpisode
 */
class DetailsPresenterTest extends TestCase
{
    public function setUp()
    {
        Chronos::setTestNow(Chronos::now());
    }
    public function tearDown()
    {
        Chronos::setTestNow();
    }

    public function testReleaseDateIsNull()
    {
        $episode = EpisodeBuilder::any()->with(['releaseDate' => null])->build();
        $presenter = new DetailsPresenter($this->createMock(PlayTranslationsHelper::class), $this->createMock(UrlGeneratorInterface::class), $episode, []);
        $this->assertNull($presenter->getReleaseDate());
    }

    public function testReleaseDateIsADate()
    {
        $releaseDate = new PartialDate(2012);
        $episode = EpisodeBuilder::any()->with(['releaseDate' => $releaseDate])->build();
        $presenter = new DetailsPresenter($this->createMock(PlayTranslationsHelper::class), $this->createMock(UrlGeneratorInterface::class), $episode, []);
        $this->assertInstanceOf(DateTime::class, $presenter->getReleaseDate());
    }

    /**
     * @dataProvider indefiniteProvider
     */
    public function testIndefiniteAvailability(?Chronos $streamableUntil, bool $availableIndefinately)
    {
        $episode = EpisodeBuilder::any()->with(['streamableUntil' => $streamableUntil])->build();
        $presenter = new DetailsPresenter($this->createMock(PlayTranslationsHelper::class), $this->createMock(UrlGeneratorInterface::class), $episode, []);
        $this->assertSame($availableIndefinately, $presenter->isAvailableIndefinitely());
    }

    public function indefiniteProvider(): array
    {
        return [
            'no-limit' => [null, true],
            'over-a-year' => [new Chronos('+2 years'), true],
            'within-a-year' => [new Chronos('+2 months'), false],
        ];
    }

    /**
     * @dataProvider streamableTimeProvider
     */
    public function testStreamableTimeRemaining(Episode $episode, string $string)
    {
        $interval = $episode->getStreamableUntil()->diff(Chronos::now());
        $playTranslationsHelper = $this->createMock(PlayTranslationsHelper::class);
        $playTranslationsHelper->expects($this->once())->method('timeIntervalToWords')->with($interval, false, $string);
        $presenter = new DetailsPresenter($playTranslationsHelper, $this->createMock(UrlGeneratorInterface::class), $episode, []);
        $presenter->getStreamableTimeRemaining();
    }

    public function streamableTimeProvider(): array
    {
        $streamableUntil = new Chronos('+1 month');
        $options = [];
        $audioType = $this->createMock(Episode::class);
        $audioType->method('getStreamableUntil')->willReturn($streamableUntil);
        $audioType->method('getMediaType')->willReturn('audio');
        $options['audio-type'] = [$audioType, 'iplayer_listen_remaining'];
        $nonAudioType = $this->createMock(Episode::class);
        $nonAudioType->method('getStreamableUntil')->willReturn($streamableUntil);
        $nonAudioType->method('getMediaType')->willReturn('something else');
        $options['non-audio-type'] = [$nonAudioType, 'iplayer_watch_remaining'];
        $radio = $this->createMock(Episode::class);
        $radio->method('getStreamableUntil')->willReturn($streamableUntil);
        $radio->method('getMediaType')->willReturn('');
        $radio->method('isRadio')->willReturn(true);
        $options['radio'] = [$radio, 'iplayer_listen_remaining'];
        $tv = $this->createMock(Episode::class);
        $tv->method('getStreamableUntil')->willReturn($streamableUntil);
        $tv->method('getMediaType')->willReturn('');
        $tv->method('isRadio')->willReturn(false);
        $tv->method('isTv')->willReturn(true);
        $options['tv'] = [$tv, 'iplayer_watch_remaining'];
        $none = $this->createMock(Episode::class);
        $none->method('getStreamableUntil')->willReturn($streamableUntil);
        $none->method('getMediaType')->willReturn('');
        $none->method('isRadio')->willReturn(false);
        $none->method('isTv')->willReturn(false);
        $options['none'] = [$none, 'iplayer_play_remaining'];

        return $options;
    }

    public function testADVersionIsAvailable()
    {
        $versions = [
            $this->createVersionMock('foo', true),
            $this->createVersionMock('bar', false),
            $this->createVersionMock('DubbedAudioDescribed', true),
        ];
        $presenter = new DetailsPresenter($this->createMock(PlayTranslationsHelper::class), $this->createMock(UrlGeneratorInterface::class), EpisodeBuilder::any()->build(), $versions);
        $this->assertTrue($presenter->hasAvailableAudioDescribedVersion());
    }

    public function testADVersionIsUnavailable()
    {
        $versions = [
            $this->createVersionMock('foo', false),
            $this->createVersionMock('bar', true),
            $this->createVersionMock('DubbedAudioDescribed', false),
        ];
        $presenter = new DetailsPresenter($this->createMock(PlayTranslationsHelper::class), $this->createMock(UrlGeneratorInterface::class), EpisodeBuilder::any()->build(), $versions);
        $this->assertFalse($presenter->hasAvailableAudioDescribedVersion());
    }

    public function testSignedVersionIsAvailable()
    {
        $versions = [
            $this->createVersionMock('foo', false),
            $this->createVersionMock('bar', true),
            $this->createVersionMock('Signed', true),
        ];
        $presenter = new DetailsPresenter($this->createMock(PlayTranslationsHelper::class), $this->createMock(UrlGeneratorInterface::class), EpisodeBuilder::any()->build(), $versions);
        $this->assertTrue($presenter->hasAvailableSignedVersion());
    }

    public function testSignedVersionIsUnavailable()
    {
        $versions = [
            $this->createVersionMock('foo', true),
            $this->createVersionMock('bar', true),
            $this->createVersionMock('Signed', false),
        ];
        $presenter = new DetailsPresenter($this->createMock(PlayTranslationsHelper::class), $this->createMock(UrlGeneratorInterface::class), EpisodeBuilder::any()->build(), $versions);
        $this->assertFalse($presenter->hasAvailableSignedVersion());
    }

    public function testPodcastFileName()
    {
        $ancestors = [];
        for ($i = 'a'; $i <= 'e'; $i++) {
            $ancestor = $this->createMock(CoreEntity::class);
            $ancestor->method('getTitle')->willReturn($i);
            $ancestors[] = $ancestor;
        }
        $episode = $this->createMock(Episode::class);
        $episode->method('getAncestry')->willReturn($ancestors);
        $episode->method('getPid')->willReturn(new Pid('b000c111'));
        $presenter = new DetailsPresenter($this->createMock(PlayTranslationsHelper::class), $this->createMock(UrlGeneratorInterface::class), $episode, []);
        $this->assertEquals('e, d, c, b, a - b000c111.mp3', $presenter->getPodcastFileName());
    }
    public function testRetrivingPodcastUrls()
    {
        $versionPid = new Pid('z000y111');
        $version = $this->createVersionMock('Podcast', false);
        $version->method('getPid')->willReturn($versionPid);
        $episode = EpisodeBuilder::any()
            ->with(['downloadableMediaSets' => ['audio-nondrm-download', 'audio-nondrm-download-low']])
            ->build();
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->exactly(2))
            ->method('generate')
            ->withConsecutive(
                ['podcast_download', ['pid' => $versionPid]],
                ['podcast_download_low', ['pid' => $versionPid]]
            );
        $presenter = new DetailsPresenter($this->createMock(PlayTranslationsHelper::class), $urlGenerator, $episode, [$version]);
        $urls = $presenter->getPodcastUrls();
        $this->assertCount(2, $urls);
        $this->assertArrayHasKey('podcast_128kbps_quality', $urls);
        $this->assertArrayHasKey('podcast_64kbps_quality', $urls);
    }

    private function createVersionMock(string $type, bool $isStreamable)
    {
        $version = $this->createMock(Version::class);
        $version->method('getVersionTypes')->willReturn([new VersionType($type, $type)]);
        $version->method('isStreamable')->willReturn($isStreamable);

        return $version;
    }
}
