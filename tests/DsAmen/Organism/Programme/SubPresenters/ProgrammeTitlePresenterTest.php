<?php
declare(strict_types=1);

namespace Tests\App\DsAmen\Organism\Programme\SubPresenters;

use App\DsAmen\Organism\Programme\SubPresenters\ProgrammeTitlePresenter;
use App\DsShared\Helpers\TitleLogicHelper;
use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\App\DsAmen\Organism\Programme\BaseProgrammeSubPresenterTest;

class ProgrammeTitlePresenterTest extends BaseProgrammeSubPresenterTest
{
    /** @var Clip|PHPUnit_Framework_MockObject_MockObject */
    private $mockClip;

    /** @var Brand|PHPUnit_Framework_MockObject_MockObject */
    private $mockContext;

    /** @var  UrlGeneratorInterface|PHPUnit_Framework_MockObject_MockObject */
    private $router;

    /** @var  TitleLogicHelper|PHPUnit_Framework_MockObject_MockObject */
    private $mockTitleLogicHelper;

    protected function setUp()
    {
        $this->mockClip = $this->createMockClip();
        $this->mockContext = $this->createMockBrand();
        $this->mockTitleLogicHelper = $this->createMock(TitleLogicHelper::class);
        $this->router = $this->createRouter();
    }

    /** @dataProvider getBrandingClassProvider */
    public function testGetBrandingClass(string $brandingName, bool $textColourOnTitleLink, string $expected): void
    {
        $titlePresenter = new ProgrammeTitlePresenter(
            $this->mockClip,
            $this->router,
            $this->mockTitleLogicHelper,
            ['branding_name' => $brandingName, 'text_colour_on_title_link' => $textColourOnTitleLink]
        );

        $this->assertSame($expected, $titlePresenter->getBrandingClass());
    }

    public function getBrandingClassProvider(): array
    {
        return [
            'No branding return empty' => ['', true, ''],
            'No text colour on title link return empty' => ['secondary', false, ''],
            'Branding and text colour on title link return class' => ['secondary', true, 'br-secondary-text-ontext'],
        ];
    }

    /** @dataProvider getLinkLocationPrefixProvider */
    public function testGetLinkLocationPrefix(bool $forceIplayerLinking, string $expected): void
    {
        $titlePresenter = new ProgrammeTitlePresenter(
            $this->mockClip,
            $this->router,
            $this->mockTitleLogicHelper,
            ['force_iplayer_linking' => $forceIplayerLinking]
        );

        $this->assertSame($expected, $titlePresenter->getLinkLocationPrefix());
    }

    public function getLinkLocationPrefixProvider(): array
    {
        return [
            'Forcing iPlayer linking' => [true, 'map_iplayer_'],
            'Not forcing iPlayer linking' => [false, 'programmeobject_'],
        ];
    }

    /** @dataProvider getMainAndSubTitlesProvider */
    public function testGetMainAndSubTitles(array $titleAncestry, string $subTitle): void
    {
        $this->mockTitleLogicHelper
            ->method('getOrderedProgrammesForTitle')
            ->with($this->mockClip, $this->mockContext, 'item::ancestry')
            ->willReturn([$this->mockClip, $titleAncestry]);

        $titlePresenter = new ProgrammeTitlePresenter(
            $this->mockClip,
            $this->router,
            $this->mockTitleLogicHelper,
            [
                'context_programme' => $this->mockContext,
                'title_format' => 'item::ancestry',
            ]
        );

        $this->assertSame('Clip 1', $titlePresenter->getMainTitle());
        $this->assertSame($subTitle, $titlePresenter->getSubTitle());
    }

    public function getMainAndSubTitlesProvider(): array
    {
        $mockSeries = $this->createMock(Series::class);
        $mockSeries->method('getTitle')->willReturn('Series 1');

        $mockEpisode = $this->createMock(Episode::class);
        $mockEpisode->method('getTitle')->willReturn('Episode 1');

        return [
            'No parents' => [[], ''],
            'One parent' => [[$mockEpisode], 'Episode 1'],
            'Multiple parents' => [[$mockSeries, $mockEpisode], 'Series 1, Episode 1'],
        ];
    }

    /** @dataProvider getUrlProvider */
    public function testGetUrl(Programme $programme, bool $forceIplayerLinking, string $expected): void
    {
        $titlePresenter = new ProgrammeTitlePresenter(
            $programme,
            $this->router,
            $this->mockTitleLogicHelper,
            [
                'context_programme' => $this->mockContext,
                'title_format' => 'item::ancestry',
                'force_iplayer_linking' => $forceIplayerLinking,
            ]
        );

        $this->assertSame($expected, $titlePresenter->getUrl());
    }

    public function getUrlProvider(): array
    {
        $tvEpisode = $this->createMockTvEpisode();
        $radioEpisode = $this->createMockRadioEpisode();
        $clip = $this->createMockClip();
        $brand = $this->createMockBrand();

        return [
            'Forcing TV Episode links to iPlayer' => [$tvEpisode, true, 'http://localhost/iplayer/episode/p0000002'],
            'Forcing Radio Episodes doesn\'t link to iPlayer' => [$radioEpisode, true, 'http://localhost/programmes/p0000003'],
            'Clip links to Find By Pid' => [$clip, false, 'http://localhost/programmes/p0000001'],
            'Brand links to Find By Pid' => [$brand, false, 'http://localhost/programmes/br000001'],
        ];
    }

    /** @expectedException App\Exception\InvalidOptionException */
    public function testUsingInvalidTextColourOnTitleLinkValueThrowsException(): void
    {
        new ProgrammeTitlePresenter(
            $this->mockClip,
            $this->router,
            $this->mockTitleLogicHelper,
            ['text_colour_on_title_link' => null]
        );
    }
}
