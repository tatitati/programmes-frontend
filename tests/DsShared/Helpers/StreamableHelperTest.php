<?php
declare (strict_types = 1);

namespace Tests\App\DsShared\Helpers;

use App\Builders\ClipBuilder;
use App\Builders\EpisodeBuilder;
use App\Builders\MasterBrandBuilder;
use App\Builders\NetworkBuilder;
use App\DsShared\Helpers\StreamableHelper;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum;
use BBC\ProgrammesPagesService\Domain\Enumeration\NetworkMediumEnum;
use BBC\ProgrammesPagesService\Domain\ValueObject\Nid;
use PHPUnit\Framework\TestCase;

class StreamableHelperTest extends TestCase
{
    /**
     * @dataProvider itemToRouteProvider
     */
    public function testGetRouteForProgramme(ProgrammeItem $programmeItem, string $expectedOutcome)
    {
        $helper = new StreamableHelper();
        $this->assertSame($expectedOutcome, $helper->getRouteForProgrammeItem($programmeItem));
    }

    /**
     * @dataProvider unknownMediaTypeProvider
     */
    public function testEpisodeWithUnknownMediaTypeIsDetectedAsAudioCorrectly(string $networkMedium, bool $expectedOutcome)
    {
        $helper = new StreamableHelper();
        $network = NetworkBuilder::any()->with(['medium' => $networkMedium])->build();
        $masterBrand = MasterBrandBuilder::any()->with(['network' => $network])->build();
        $episode = EpisodeBuilder::any()->with(['mediaType' => MediaTypeEnum::UNKNOWN, 'masterBrand' => $masterBrand])->build();
        $this->assertSame($expectedOutcome, $helper->shouldTreatProgrammeItemAsAudio($episode));
    }

    public function testEpisodeWithUnknownMediaTypeWithNoNetworkIsNotAudio()
    {
        $helper = new StreamableHelper();
        $episode = EpisodeBuilder::any()->with(['mediaType' => MediaTypeEnum::UNKNOWN])->build();
        $this->assertSame(false, $helper->shouldTreatProgrammeItemAsAudio($episode));
    }

    /**
     * @dataProvider knownMediaTypeProvider
     */
    public function testKnownMediaTypes(string $mediaType, bool $expectedOutcome)
    {
        $helper = new StreamableHelper();
        $network = NetworkBuilder::any()->with(['medium' => NetworkMediumEnum::UNKNOWN])->build();
        $masterBrand = MasterBrandBuilder::any()->with(['network' => $network])->build();
        $episode = EpisodeBuilder::any()->with(['mediaType' => $mediaType, 'masterBrand' => $masterBrand])->build();
        $this->assertSame($expectedOutcome, $helper->shouldTreatProgrammeItemAsAudio($episode));
    }

    public function testShouldStreamViaPlayspace()
    {
        $helper = new StreamableHelper();
        $network = NetworkBuilder::any()->with(['medium' => NetworkMediumEnum::UNKNOWN, 'nid' => new Nid('bbc_radio_three')])->build();
        $masterBrand = MasterBrandBuilder::any()->with(['network' => $network, 'streamableInPlayspace' => true])->build();
        $playspaceAudioClip = ClipBuilder::any()->with(['mediaType' => MediaTypeEnum::AUDIO, 'masterBrand' => $masterBrand])->build();
        $this->assertTrue($helper->shouldStreamViaPlayspace($playspaceAudioClip));

        $episode = EpisodeBuilder::any()->with(['mediaType' => MediaTypeEnum::VIDEO, 'masterBrand' => $masterBrand])->build();
        $this->assertFalse($helper->shouldStreamViaPlayspace($episode));
    }

    public function itemToRouteProvider(): array
    {
        $videoClip = ClipBuilder::any()->with(['isStreamable' => true, 'mediaType' => MediaTypeEnum::VIDEO])->build();
        $videoEpisode = EpisodeBuilder::any()->with(['isStreamable' => true, 'mediaType' => MediaTypeEnum::VIDEO])->build();

        $audioClip = ClipBuilder::any()->with(['isStreamable' => true, 'mediaType' => MediaTypeEnum::AUDIO])->build();
        // Playspace audio clip
        $network = NetworkBuilder::any()->with(['nid' => new Nid('bbc_radio_three')])->build();
        $masterBrand = MasterBrandBuilder::any()->with(['network' => $network, 'streamableInPlayspace' => true])->build();
        $playspaceAudioClip = ClipBuilder::any()->with(['mediaType' => MediaTypeEnum::AUDIO, 'masterBrand' => $masterBrand])->build();
        $playspaceAudioEpisode = EpisodeBuilder::any()->with(['mediaType' => MediaTypeEnum::AUDIO, 'masterBrand' => $masterBrand])->build();

        return [
            'episode-video' => [$videoEpisode, 'iplayer_play'],
            'clip-not-audio' => [$videoClip, 'find_by_pid'],
            'clip-audio' => [$audioClip, 'find_by_pid'],
            'playspace-clip-audio' => [$playspaceAudioClip, 'playspace_play'],
            'playspace-episode' => [$playspaceAudioEpisode, 'playspace_play'],
        ];
    }

    public function unknownMediaTypeProvider(): array
    {
        return [
            'network-unknown' => [NetworkMediumEnum::UNKNOWN, false],
            'network-radio' => [NetworkMediumEnum::RADIO, true],
            'network-tv' => [NetworkMediumEnum::TV, false],
        ];
    }

    public function knownMediaTypeProvider(): array
    {
        return [
            'video' => [MediaTypeEnum::VIDEO, false],
            'audio' => [MediaTypeEnum::AUDIO, true],
        ];
    }
}
