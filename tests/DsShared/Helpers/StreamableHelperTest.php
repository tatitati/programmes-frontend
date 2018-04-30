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
    public function testGetRouteForProgramme(ProgrammeItem $programmeItem, bool $isAudio, string $expectedOutcome)
    {
        $helper = $this->getMockBuilder(StreamableHelper::class)->setMethods(['shouldTreatProgrammeItemAsAudio'])->getMock();
        $helper->method('shouldTreatProgrammeItemAsAudio')->willReturn($isAudio);
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
        $masterBrand = MasterBrandBuilder::any()->with(['network' => $network])->build();
        $playspaceAudioClip = ClipBuilder::any()->with(['mediaType' => MediaTypeEnum::AUDIO, 'masterBrand' => $masterBrand])->build();
        $this->assertTrue($helper->shouldStreamViaPlayspace($playspaceAudioClip));

        $episode = EpisodeBuilder::any()->with(['mediaType' => MediaTypeEnum::VIDEO, 'masterBrand' => $masterBrand])->build();
        $this->assertFalse($helper->shouldStreamViaPlayspace($episode));
    }

    public function itemToRouteProvider(): array
    {
        $clip = ClipBuilder::any()->build();
        $episode = EpisodeBuilder::any()->build();

        // Playspace audio clip
        $network = NetworkBuilder::any()->with(['nid' => new Nid('bbc_radio_three')])->build();
        $masterBrand = MasterBrandBuilder::any()->with(['network' => $network])->build();
        $playspaceAudioClip = ClipBuilder::any()->with(['mediaType' => MediaTypeEnum::AUDIO, 'masterBrand' => $masterBrand])->build();

        return [
            'episode-not-audio' => [$episode, false, 'iplayer_play'],
            'episode-audio' => [$episode, true, 'find_by_pid'],
            'clip-not-audio' => [$clip, false, 'find_by_pid'],
            'clip-audio' => [$clip, true, 'find_by_pid'],
            'playspace-clip-audio' => [$playspaceAudioClip, true, 'playspace_play'],
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
