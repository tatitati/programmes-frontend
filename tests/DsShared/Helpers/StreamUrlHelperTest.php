<?php
declare(strict_types = 1);

namespace Tests\App\DsShared\Helpers;

use App\Builders\AbstractBuilder;
use App\Builders\ClipBuilder;
use App\Builders\EpisodeBuilder;
use App\DsShared\Helpers\StreamUrlHelper;
use BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum;
use PHPUnit\Framework\TestCase;

class StreamUrlHelperTest extends TestCase
{
    /**
     * @dataProvider itemToRouteProvider
     */
    public function testProgrammeItemIsSentToCorrectRoute(AbstractBuilder $builder, string $mediaType, string $expectedRoute)
    {
        $programmeItem = $builder->with(['mediaType' => $mediaType])->build();
        $helper = new StreamUrlHelper();
        $this->assertSame($expectedRoute, $helper->getRouteForProgrammeItem($programmeItem));
    }

    public function itemToRouteProvider(): array
    {
        $clipBuilder = ClipBuilder::any();
        $episodeBuilder = EpisodeBuilder::any();
        return [
            'episode-video' => [$episodeBuilder, MediaTypeEnum::VIDEO, 'iplayer_play'],
            'episode-audio' => [$episodeBuilder, MediaTypeEnum::AUDIO, 'find_by_pid'],
            'clip-video' => [$clipBuilder, MediaTypeEnum::VIDEO, 'find_by_pid'],
            'clip-audio' => [$clipBuilder, MediaTypeEnum::AUDIO, 'find_by_pid'],
        ];
    }
}
