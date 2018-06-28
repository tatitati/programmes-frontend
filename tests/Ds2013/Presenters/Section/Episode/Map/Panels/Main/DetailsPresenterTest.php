<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Presenters\Section\Episode\Map\Panels\Main;

use App\Builders\EpisodeBuilder;
use App\Ds2013\Presenters\Section\Episode\Map\Panels\Main\DetailsPresenter;
use App\DsShared\Helpers\PlayTranslationsHelper;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\Entity\VersionType;
use BBC\ProgrammesPagesService\Domain\ValueObject\PartialDate;
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
        $presenter = new DetailsPresenter($this->createMock(PlayTranslationsHelper::class), $this->createMock(UrlGeneratorInterface::class), $episode, [], null);
        $this->assertNull($presenter->getReleaseDate());
    }

    public function testReleaseDateIsADate()
    {
        $releaseDate = new PartialDate(2012);
        $episode = EpisodeBuilder::any()->with(['releaseDate' => $releaseDate])->build();
        $presenter = new DetailsPresenter($this->createMock(PlayTranslationsHelper::class), $this->createMock(UrlGeneratorInterface::class), $episode, [], null);
        $this->assertInstanceOf(DateTime::class, $presenter->getReleaseDate());
    }

    /**
     * @dataProvider indefiniteProvider
     */
    public function testIndefiniteAvailability(?Chronos $streamableUntil, bool $availableIndefinately)
    {
        $episode = EpisodeBuilder::any()->with(['streamableUntil' => $streamableUntil])->build();
        $presenter = new DetailsPresenter($this->createMock(PlayTranslationsHelper::class), $this->createMock(UrlGeneratorInterface::class), $episode, [], null);
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
        $presenter = new DetailsPresenter($playTranslationsHelper, $this->createMock(UrlGeneratorInterface::class), $episode, [], null);
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
        $presenter = new DetailsPresenter($this->createMock(PlayTranslationsHelper::class), $this->createMock(UrlGeneratorInterface::class), EpisodeBuilder::any()->build(), $versions, null);
        $this->assertTrue($presenter->hasAvailableAudioDescribedVersion());
    }

    public function testADVersionIsUnavailable()
    {
        $versions = [
            $this->createVersionMock('foo', false),
            $this->createVersionMock('bar', true),
            $this->createVersionMock('DubbedAudioDescribed', false),
        ];
        $presenter = new DetailsPresenter($this->createMock(PlayTranslationsHelper::class), $this->createMock(UrlGeneratorInterface::class), EpisodeBuilder::any()->build(), $versions, null);
        $this->assertFalse($presenter->hasAvailableAudioDescribedVersion());
    }

    public function testSignedVersionIsAvailable()
    {
        $versions = [
            $this->createVersionMock('foo', false),
            $this->createVersionMock('bar', true),
            $this->createVersionMock('Signed', true),
        ];
        $presenter = new DetailsPresenter($this->createMock(PlayTranslationsHelper::class), $this->createMock(UrlGeneratorInterface::class), EpisodeBuilder::any()->build(), $versions, null);
        $this->assertTrue($presenter->hasAvailableSignedVersion());
    }

    public function testSignedVersionIsUnavailable()
    {
        $versions = [
            $this->createVersionMock('foo', true),
            $this->createVersionMock('bar', true),
            $this->createVersionMock('Signed', false),
        ];
        $presenter = new DetailsPresenter($this->createMock(PlayTranslationsHelper::class), $this->createMock(UrlGeneratorInterface::class), EpisodeBuilder::any()->build(), $versions, null);
        $this->assertFalse($presenter->hasAvailableSignedVersion());
    }

    private function createVersionMock(string $type, bool $isStreamable)
    {
        $version = $this->createMock(Version::class);
        $version->method('getVersionTypes')->willReturn([new VersionType($type, $type)]);
        $version->method('isStreamable')->willReturn($isStreamable);

        return $version;
    }
}
