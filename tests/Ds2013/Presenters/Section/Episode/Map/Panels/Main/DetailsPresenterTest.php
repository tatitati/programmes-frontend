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
