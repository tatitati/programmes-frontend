<?php
namespace Tests\App\Ds2013\Presenters\Section\Clip\Details;

use App\Builders\ClipBuilder;
use App\Builders\ContributionBuilder;
use App\Ds2013\Presenters\Section\Clip\Details\ClipDetailsPresenter;
use App\DsShared\Helpers\PlayTranslationsHelper;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use Symfony\Component\DomCrawler\Crawler;
use Tests\App\BaseTemplateTestCase;

class ClipDetailsPresenterCreditsTest extends BaseTemplateTestCase
{
    /**
     * Presenter HTML -- credits
     *
     * @dataProvider contributionsProvider
     */
    public function testCreditsPresenterIsCreatedInsideClipDetails(array $givenContributions, $shouldCreditsPresenterBeRendered)
    {
        $givenClip = ClipBuilder::any()->build();

        $crawler = $this->renderDetailsPresenter($givenClip, $givenContributions);

        $this->assertEquals($shouldCreditsPresenterBeRendered, $crawler->filter('.credits__contributions')->count());
    }

    public function contributionsProvider()
    {
        $givenContributions = [
            ContributionBuilder::any()->build(),
            ContributionBuilder::any()->build(),
        ];

        $noContributions = [];

        return [
            'Credits_block_is_displayed' => [$givenContributions, 1],
            'Credits_block_is_not_displayed' => [$noContributions, 0],
        ];
    }

    /**
     * HELPERS
     */
    private function renderDetailsPresenter(Clip $clip, array $contributions = []): Crawler
    {
        $clipDetailsPresenter = $this->presenter($clip, $contributions);
        $html = $this->presenterHtml($clipDetailsPresenter);
        return new Crawler($html);
    }

    private function presenter(Clip $clip, array $contributions = []): ClipDetailsPresenter
    {
        $stubPlayTrans = $this->createConfiguredMock(PlayTranslationsHelper::class, [
            'secondsToWords' => '2 minutes',
            'translateAvailableUntilToWords' => '6 months left to watch',
        ]);

        return new ClipDetailsPresenter($stubPlayTrans, $clip, $contributions, null, null);
    }
}
