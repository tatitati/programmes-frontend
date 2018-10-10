<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Presenters\Utilities\Cta;

use App\Builders\EpisodeBuilder;
use App\Ds2013\Presenters\Utilities\Cta\CtaPresenter;
use App\DsShared\Helpers\PlayTranslationsHelper;
use App\DsShared\Helpers\StreamableHelper;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Collection;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Gallery;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CtaPresenterTest extends TestCase
{
    public function testExceptionIsThrownWhenNonEpisodeAvailabilityIsRequested()
    {
        $coreEntity = $this->createMock(Clip::class);
        $playTranslationHelper = $this->createMock(PlayTranslationsHelper::class);
        $router = $this->createMock(UrlGeneratorInterface::class);
        $streamUrlHelper = $this->createMock(StreamableHelper::class);
        $presenter = new CtaPresenter($coreEntity, $playTranslationHelper, $router, $streamUrlHelper);
        $this->expectExceptionMessage('This CTA doesn\t need a link, why are you calling this method?');
        $presenter->getAvailabilityInWords();
    }

    public function testAvailabilityInWordsCallsHelperWhenCoreEntityIsEpisode()
    {
        $coreEntity = $this->createMock(Episode::class);
        $playTranslationHelper = $this->createMock(PlayTranslationsHelper::class);
        $playTranslationHelper->expects($this->once())
            ->method('translateAvailableUntilToWords')
            ->with($coreEntity);
        $router = $this->createMock(UrlGeneratorInterface::class);
        $streamUrlHelper = $this->createMock(StreamableHelper::class);
        $presenter = new CtaPresenter($coreEntity, $playTranslationHelper, $router, $streamUrlHelper);
        $presenter->getAvailabilityInWords();
    }

    public function testGalleryUsesImageIcon()
    {
        $coreEntity = $this->createMock(Gallery::class);
        $playTranslationHelper = $this->createMock(PlayTranslationsHelper::class);
        $router = $this->createMock(UrlGeneratorInterface::class);
        $streamUrlHelper = $this->createMock(StreamableHelper::class);
        $presenter = new CtaPresenter($coreEntity, $playTranslationHelper, $router, $streamUrlHelper);
        $this->assertEquals('image', $presenter->getMediaIconName());
    }

    public function testCollectionUsesCollectionIcon()
    {
        $coreEntity = $this->createMock(Collection::class);
        $playTranslationHelper = $this->createMock(PlayTranslationsHelper::class);
        $router = $this->createMock(UrlGeneratorInterface::class);
        $streamUrlHelper = $this->createMock(StreamableHelper::class);
        $presenter = new CtaPresenter($coreEntity, $playTranslationHelper, $router, $streamUrlHelper);
        $this->assertEquals('collection', $presenter->getMediaIconName());
    }

    public function testAudioEpisodeUsesListenIcon()
    {
        $coreEntity = $this->createMock(Episode::class);
        $playTranslationHelper = $this->createMock(PlayTranslationsHelper::class);
        $router = $this->createMock(UrlGeneratorInterface::class);
        $streamUrlHelper = $this->createMock(StreamableHelper::class);
        $streamUrlHelper->expects($this->once())
            ->method('shouldTreatProgrammeItemAsAudio')
            ->with($coreEntity)
            ->willReturn(true);
        $presenter = new CtaPresenter($coreEntity, $playTranslationHelper, $router, $streamUrlHelper);
        $this->assertEquals('listen', $presenter->getMediaIconName());
    }

    public function testAudioClipUsesListenIcon()
    {
        $coreEntity = $this->createMock(Clip::class);
        $playTranslationHelper = $this->createMock(PlayTranslationsHelper::class);
        $router = $this->createMock(UrlGeneratorInterface::class);
        $streamUrlHelper = $this->createMock(StreamableHelper::class);
        $streamUrlHelper->expects($this->once())
            ->method('shouldTreatProgrammeItemAsAudio')
            ->with($coreEntity)
            ->willReturn(true);
        $presenter = new CtaPresenter($coreEntity, $playTranslationHelper, $router, $streamUrlHelper);
        $this->assertEquals('listen', $presenter->getMediaIconName());
    }

    public function testVideoEpisodeUsesIplayerIcon()
    {
        $coreEntity = $this->createMock(Episode::class);
        $playTranslationHelper = $this->createMock(PlayTranslationsHelper::class);
        $router = $this->createMock(UrlGeneratorInterface::class);
        $streamUrlHelper = $this->createMock(StreamableHelper::class);
        $streamUrlHelper->expects($this->once())
            ->method('shouldTreatProgrammeItemAsAudio')
            ->with($coreEntity)
            ->willReturn(false);
        $presenter = new CtaPresenter($coreEntity, $playTranslationHelper, $router, $streamUrlHelper);
        $this->assertEquals('iplayer', $presenter->getMediaIconName());
    }

    public function testVideoClipUsesPlayIcon()
    {
        $coreEntity = $this->createMock(Clip::class);
        $playTranslationHelper = $this->createMock(PlayTranslationsHelper::class);
        $router = $this->createMock(UrlGeneratorInterface::class);
        $streamUrlHelper = $this->createMock(StreamableHelper::class);
        $streamUrlHelper->expects($this->once())
            ->method('shouldTreatProgrammeItemAsAudio')
            ->with($coreEntity)
            ->willReturn(false);
        $presenter = new CtaPresenter($coreEntity, $playTranslationHelper, $router, $streamUrlHelper);
        $this->assertEquals('play', $presenter->getMediaIconName());
    }

    public function testIplayerClassIsAddedForVideoEpisodes()
    {
        $coreEntity = $this->createMock(Episode::class);
        $playTranslationHelper = $this->createMock(PlayTranslationsHelper::class);
        $router = $this->createMock(UrlGeneratorInterface::class);
        $streamUrlHelper = $this->createMock(StreamableHelper::class);
        $streamUrlHelper->expects($this->once())
            ->method('shouldTreatProgrammeItemAsAudio')
            ->with($coreEntity)
            ->willReturn(false);
        $presenter = new CtaPresenter($coreEntity, $playTranslationHelper, $router, $streamUrlHelper);
        $this->assertEquals('iplayer-icon', $presenter->getProductClasses());
    }

    public function testUrlForNonEpisodeThrowsException()
    {
        $coreEntity = $this->createMock(Gallery::class);
        $playTranslationHelper = $this->createMock(PlayTranslationsHelper::class);
        $router = $this->createMock(UrlGeneratorInterface::class);
        $streamUrlHelper = $this->createMock(StreamableHelper::class);
        $presenter = new CtaPresenter($coreEntity, $playTranslationHelper, $router, $streamUrlHelper);
        $this->expectExceptionMessage('This CTA doesn\t need a link, why are you calling this method?');
        $presenter->getUrl();
    }

    public function testUrlForEpisode()
    {
        $pid = new Pid('bcdf1234');
        $coreEntity = $this->createMock(Episode::class);
        $coreEntity->expects($this->once())
            ->method('getPid')
            ->willReturn($pid);
        $playTranslationHelper = $this->createMock(PlayTranslationsHelper::class);
        $router = $this->createMock(UrlGeneratorInterface::class);
        $router->expects($this->once())
            ->method('generate')
            ->with('anything', ['pid' => $pid], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('anything');
        $streamUrlHelper = $this->createMock(StreamableHelper::class);
        $streamUrlHelper->expects($this->once())
            ->method('getRouteForProgrammeItem')
            ->with($coreEntity)
            ->willReturn('anything');
        $presenter = new CtaPresenter($coreEntity, $playTranslationHelper, $router, $streamUrlHelper);
        $presenter->getUrl();
    }

    /**
     * @group cta__data_link_track
     * @dataProvider optionsProvider
     */
    public function testDataLinkIsConfigurable(array $givenOptions, string $expectedDataLinkTrack)
    {
        $presenter = $this->presenterWithOptions($givenOptions);

        $this->assertEquals($expectedDataLinkTrack, $presenter->getOption('data_link_track'));
    }

    public function optionsProvider(): array
    {
        return [
            'data-link-track-using-default-value' => [
                [],
                'programmeobjectlink=cta',
            ],
            'data-link-track-using-CUSTOM-value' => [
                ['data_link_track' => 'custom-link-track'],
                'custom-link-track',
            ],
        ];
    }

    private function presenterWithOptions(array $options): CtaPresenter
    {
        $episode = EpisodeBuilder::any()->build();
        $dummy1 = $this->createMock(PlayTranslationsHelper::class);
        $dummy2 = $this->createMock(UrlGeneratorInterface::class);
        $dummy3 = $this->createMock(StreamableHelper::class);

        return new CtaPresenter($episode, $dummy1, $dummy2, $dummy3, $options);
    }
}
