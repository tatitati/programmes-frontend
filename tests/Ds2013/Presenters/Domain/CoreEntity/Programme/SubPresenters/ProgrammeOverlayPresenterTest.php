<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Presenters\Domain\CoreEntity\Programme\SubPresenters;

use App\Ds2013\Presenters\Domain\CoreEntity\Programme\SubPresenters\ProgrammeOverlayPresenter;
use App\DsShared\Helpers\PlayTranslationsHelper;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollectionBuilder;

class ProgrammeOverlayPresenterTest extends TestCase
{
    private $router;

    private $mockTranslationsHelper;

    public function setUp()
    {
        $routeCollectionBuilder = new RouteCollectionBuilder();
        $routeCollectionBuilder->add('/programmes/{pid}', '', 'find_by_pid');
        $routeCollectionBuilder->add('/iplayer/episode/{pid}', '', 'iplayer_play');

        $this->router = new UrlGenerator(
            $routeCollectionBuilder->build(),
            new RequestContext()
        );

        $this->mockTranslationsHelper = $this->createMock(PlayTranslationsHelper::class);
    }

    /**
     * @dataProvider mediaIconNameProvider
     */
    public function testGetMediaIconName($isRadio, $expectedResult)
    {
        $programme = $this->createMock(Episode::class);
        $programme->expects($this->once())
            ->method('isRadio')
            ->willReturn($isRadio);
        $programmeOverlayPresenter = new ProgrammeOverlayPresenter(
            $this->router,
            $this->mockTranslationsHelper,
            $programme
        );
        $this->assertEquals($expectedResult, $programmeOverlayPresenter->getMediaIconName());
    }

    public function mediaIconNameProvider()
    {
        return [
            [true, 'iplayer-radio'],
            [false, 'iplayer'],
        ];
    }


    public function testGetPlaybackUrlIplayer()
    {
        $programmeItem = $this->playbackUrlProgramme(Episode::class, 'b0000002', false, false);
        $programmeOverlayPresenter = new ProgrammeOverlayPresenter(
            $this->router,
            $this->mockTranslationsHelper,
            $programmeItem
        );
        $this->assertEquals('http://localhost/iplayer/episode/b0000002', $programmeOverlayPresenter->getPlaybackUrl());
    }

    /**
     * @dataProvider playbackUrlProgrammesDataProvider
     */
    public function testGetPlaybackUrlProgrammes(ProgrammeItem $programmeItem, $expectedUrl)
    {
        $programmeOverlayPresenter = new ProgrammeOverlayPresenter(
            $this->router,
            $this->mockTranslationsHelper,
            $programmeItem
        );
        $this->assertEquals($expectedUrl, $programmeOverlayPresenter->getPlaybackUrl());
    }

    public function playbackUrlProgrammesDataProvider()
    {
        return [
            [// Radio episode
                $this->playbackUrlProgramme(Episode::class, 'b0000001', true, true),
                'http://localhost/programmes/b0000001#play', // Expected URL for programme
            ],
            [// Audio episode
                $this->playbackUrlProgramme(Episode::class, 'b0000001', false, true),
                'http://localhost/programmes/b0000001#play', // Expected URL for programme
            ],
            [// Radio clip
                $this->playbackUrlProgramme(Clip::class, 'p0000003', true, true),
                'http://localhost/programmes/p0000003#play', // Expected URL for programme
            ],
            [// TV Clip
                $this->playbackUrlProgramme(Clip::class, 'p0000003', false, false),
                'http://localhost/programmes/p0000003#play', // Expected URL for programme
            ],
        ];
    }

    private function playbackUrlProgramme(string $type, string $pid, bool $isRadio, bool $isAudio)
    {
        $programme = $this->createMock($type);
        $programme->method('getPid')->willReturn(new Pid($pid));
        $programme->method('isRadio')->willReturn($isRadio);
        $programme->method('isAudio')->willReturn($isAudio);
        return $programme;
    }
}
