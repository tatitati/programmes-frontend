<?php
declare(strict_types=1);

namespace Tests\App\DsAmen\Organism\Programme\SubPresenters;

use App\DsAmen\Organism\Programme\SubPresenters\ProgrammeCtaPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\App\DsAmen\Organism\Programme\BaseProgrammeSubPresenterTest;

class ProgrammeCtaPresenterTest extends BaseProgrammeSubPresenterTest
{
    /** @var UrlGeneratorInterface */
    private $router;

    protected function setUp()
    {
        $this->router = $this->createRouter();
    }

    /** @dataProvider getDurationProvider */
    public function testGetDuration(ProgrammeItem $programme, int $expected): void
    {
        $ctaPresenter = new ProgrammeCtaPresenter($programme, $this->router);
        $this->assertSame($expected, $ctaPresenter->getDuration());
    }

    public function getDurationProvider(): array
    {
        $tvEpisode = $this->createMockTvEpisode();
        $clip = $this->createMockClip();
        $radioEpisode = $this->createMockRadioEpisode();

        return [
            "TV episode doesn't show duration" => [$tvEpisode, 0],
            "Clip shows duration" => [$clip, 10],
            "Radio episode shows duration" => [$radioEpisode, 30],
        ];
    }

    /** @dataProvider getLinkLocationPrefixProvider */
    public function testGetLinkLocationPrefix(bool $forceIplayerLinking, string $expected): void
    {
        $mockClip = $this->createMockClip();

        $ctaPresenter = new ProgrammeCtaPresenter(
            $mockClip,
            $this->router,
            ['force_iplayer_linking' => $forceIplayerLinking]
        );

        $this->assertSame($expected, $ctaPresenter->getLinkLocationPrefix());
    }

    public function getLinkLocationPrefixProvider(): array
    {
        return [
            'Forcing iPlayer linking' => [true, 'map_iplayer_'],
            'Not forcing iPlayer linking' => [false, 'programmeobject_'],
        ];
    }

    /** @dataProvider getMediaIconNameProvider */
    public function testGetMediaIconName(ProgrammeItem $programme, string $expected): void
    {
        $ctaPresenter = new ProgrammeCtaPresenter($programme, $this->router);
        $this->assertSame($expected, $ctaPresenter->getMediaIconName());
    }

    public function getMediaIconNameProvider(): array
    {
        $tvEpisode = $this->createMockTvEpisode();
        $clip = $this->createMockClip();
        $radioEpisode = $this->createMockRadioEpisode();

        return [
            'TV episode shows iPlayer CTA icon' => [$tvEpisode, 'iplayer'],
            'Clip shows play CTA icon' => [$clip, 'play'],
            'Radio episode shows iPlayer Radio CTA icon' => [$radioEpisode, 'iplayer-radio'],
        ];
    }

    /** @dataProvider getPlayTranslationProvider */
    public function testGetPlayTranslation(ProgrammeItem $programme, string $expected): void
    {
        $ctaPresenter = new ProgrammeCtaPresenter($programme, $this->router);
        $this->assertSame($expected, $ctaPresenter->getLabelTranslation());
    }

    public function getPlayTranslationProvider(): array
    {
        $tvEpisode = $this->createMockTvEpisode();
        $clip = $this->createMockClip();
        $radioEpisode = $this->createMockRadioEpisode();

        return [
            'TV episode shows play episode translation' => [$tvEpisode, 'iplayer_play_episode'],
            'Clip shows play clip translation' => [$clip, 'iplayer_play_clip'],
            'Radio episode shows play episode translation' => [$radioEpisode, 'iplayer_play_episode'],
        ];
    }

    /** @dataProvider getUrlProvider */
    public function testGetUrl(ProgrammeItem $programme, string $expected): void
    {
        $ctaPresenter = new ProgrammeCtaPresenter($programme, $this->router);
        $this->assertSame($expected, $ctaPresenter->getUrl());
    }

    public function getUrlProvider(): array
    {
        $tvEpisode = $this->createMockTvEpisode();
        $audioTvEpisode = $this->createMockTvEpisode(true);
        $radioEpisode = $this->createMockRadioEpisode();
        $clip = $this->createMockClip();

        return [
            'TV Episode links to iPlayer' => [
                $tvEpisode,
                'http://localhost/iplayer/episode/' . $tvEpisode->getPid(),
            ],
            'Audio TV Episode links to find by pid with play anchor' => [
                $audioTvEpisode,
                'http://localhost/programmes/' . $audioTvEpisode->getPid() . '#play',
            ],
            'Radio episode links to find by pid with play anchor' => [
                $radioEpisode,
                'http://localhost/programmes/' . $radioEpisode->getPid() . '#play',
            ],
            'Clip links to find by pid' => [
                $clip, 'http://localhost/programmes/' . $clip->getPid(),
            ],
        ];
    }
}
