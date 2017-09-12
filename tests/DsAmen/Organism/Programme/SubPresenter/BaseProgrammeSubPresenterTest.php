<?php
declare(strict_types=1);

namespace Tests\App\DsAmen\Organism\Programme\SubPresenter;

use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollectionBuilder;

abstract class BaseProgrammeSubPresenterTest extends TestCase
{
    /** @return Clip|PHPUnit_Framework_MockObject_MockObject */
    protected function createMockClip()
    {
        $mockClip = $this->createMock(Clip::class);
        $mockClip->method('getTitle')->willReturn('Clip 1');
        $mockClip->method('getPid')->willReturn(new Pid('p0000001'));
        $mockClip->method('getDuration')->willReturn(10);

        return $mockClip;
    }

    /** @return Episode|PHPUnit_Framework_MockObject_MockObject */
    protected function createMockTvEpisode(bool $isAudio = false)
    {
        $mockTvEpisode = $this->createMock(Episode::class);
        $mockTvEpisode->method('isTv')->willReturn(true);
        $mockTvEpisode->method('isRadio')->willReturn(false);
        $mockTvEpisode->method('getDuration')->willReturn(20);
        $mockTvEpisode->method('getPid')->willReturn(new Pid('p0000002'));
        $mockTvEpisode->method('isAudio')->willReturn($isAudio);

        return $mockTvEpisode;
    }

    /** @return Episode|PHPUnit_Framework_MockObject_MockObject */
    protected function createMockRadioEpisode()
    {
        $mockRadioEpisode = $this->createMock(Episode::class);
        $mockRadioEpisode->method('isTv')->willReturn(false);
        $mockRadioEpisode->method('isRadio')->willReturn(true);
        $mockRadioEpisode->method('getDuration')->willReturn(30);
        $mockRadioEpisode->method('getPid')->willReturn(new Pid('p0000003'));

        return $mockRadioEpisode;
    }

    /** @return Brand|PHPUnit_Framework_MockObject_MockObject */
    protected function createMockBrand()
    {
        $mockBrand = $this->createMock(Brand::class);
        $mockBrand->method('getTitle')->willReturn('Brand 1');
        $mockBrand->method('getPid')->willReturn(new Pid('br000001'));

        return $mockBrand;
    }

    protected function createRouter(): UrlGenerator
    {
        $routeCollectionBuilder = new RouteCollectionBuilder();
        $routeCollectionBuilder->add('/programmes/{pid}', '', 'find_by_pid');
        $routeCollectionBuilder->add('/iplayer/episode/{pid}', '', 'iplayer_play');

        return new UrlGenerator(
            $routeCollectionBuilder->build(),
            new RequestContext()
        );
    }
}
