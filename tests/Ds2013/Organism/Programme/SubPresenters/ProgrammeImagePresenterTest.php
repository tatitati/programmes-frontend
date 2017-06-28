<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Organism\Programme\SubPresenters;

use App\Ds2013\Helpers\PlayTranslationsHelper;
use App\Ds2013\Organism\Programme\SubPresenters\ProgrammeImagePresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Tests\App\TwigEnvironmentProvider;

class ProgrammeImagePresenterTest extends TestCase
{
    private $router;

    private $mockTranslationsHelper;

    public function setUp()
    {
        $routeCollectionBuilder = new RouteCollectionBuilder();
        $routeCollectionBuilder->add('/programmes/{pid}', '', 'find_by_pid');
        $routeCollectionBuilder->add('/iplayer/{pid}', '', 'iplayer_play');

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
        $programmeImagePresenter = new ProgrammeImagePresenter(
            $this->router,
            $this->mockTranslationsHelper,
            $programme
        );
        $this->assertEquals($expectedResult, $programmeImagePresenter->getMediaIconName());
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
        $programmeImagePresenter = new ProgrammeImagePresenter(
            $this->router,
            $this->mockTranslationsHelper,
            $programmeItem
        );
        $this->assertEquals('/iplayer/b0000002', $programmeImagePresenter->getPlaybackUrl());
    }

    /**
     * @dataProvider playbackUrlProgrammesDataProvider
     */
    public function testGetPlaybackUrlProgrammes(ProgrammeItem $programmeItem, $expectedUrl)
    {
        $programmeImagePresenter = new ProgrammeImagePresenter(
            $this->router,
            $this->mockTranslationsHelper,
            $programmeItem
        );
        $this->assertEquals($expectedUrl, $programmeImagePresenter->getPlaybackUrl());
    }

    public function playbackUrlProgrammesDataProvider()
    {
        return [
            [// Radio episode
                $this->playbackUrlProgramme(Episode::class, 'b0000001', true, true),
                '/programmes/b0000001#play', // Expected URL for programme
            ],
            [// Audio episode
                $this->playbackUrlProgramme(Episode::class, 'b0000001', false, true),
                '/programmes/b0000001#play', // Expected URL for programme
            ],
            [// Radio clip
                $this->playbackUrlProgramme(Clip::class, 'p0000003', true, true),
                '/programmes/p0000003#play', // Expected URL for programme
            ],
            [// TV Clip
                $this->playbackUrlProgramme(Clip::class, 'p0000003', false, false),
                '/programmes/p0000003#play', // Expected URL for programme
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
