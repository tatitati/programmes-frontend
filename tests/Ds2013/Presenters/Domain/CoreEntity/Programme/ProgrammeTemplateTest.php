<?php
declare(strict_types = 1);
namespace Tests\App\Ds2013\Presenters\Domain\CoreEntity\Programme;

use App\Builders\EpisodeBuilder;
use App\Builders\MasterBrandBuilder;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use Cake\Chronos\Chronos;
use Tests\App\BaseTemplateTestCase;
use Tests\App\DataFixtures\PagesService\EpisodesFixtures;
use Tests\App\TwigEnvironmentProvider;

class ProgrammeTemplateTest extends BaseTemplateTestCase
{
    public function setUp()
    {
        $now = new Chronos('2017-06-01 12:00:00');
        ApplicationTime::setTime($now->getTimestamp());
    }

    public function testEastendersEpisode()
    {
        $programme = EpisodesFixtures::eastendersAvailable();
        $presenterFactory = TwigEnvironmentProvider::ds2013PresenterFactory();
        $presenter = $presenterFactory->programmePresenter($programme, []);
        $crawler = $this->presenterCrawler($presenter);

        $outerDiv = $crawler->filterXPath('//div');
        // Test outer div classes
        $this->assertHasClasses('programme programme--tv programme--episode block-link', $outerDiv, 'Outer div classes');
        $this->assertEquals('p0000001', $outerDiv->attr('data-pid'));

        // Test image container and lazy loaded image
        $imageContainer = $crawler->filter('.programme__img');
        $this->assertHasClasses(
            'programme__img 1/4@bpb1 1/4@bpb2 1/3@bpw programme__img--available programme__img--hasimage',
            $imageContainer,
            'Image container div classes'
        );
        $imageLazy = $imageContainer->filter('.lazyload');
        $this->assertCount(1, $imageLazy);
        $this->assertContains('p01vg679', $imageLazy->attr('data-srcset'));

        //Test overlay link and icon
        $overlayDiv = $crawler->filter('.cta__overlay');
        $this->assertCount(1, $overlayDiv);
        $this->assertHasClasses('cta__overlay', $overlayDiv, 'Overlay container classes');

        $overlayLink = $overlayDiv->filterXPath('//a')->first();
        $this->assertEquals('https://localhost/iplayer/episode/p0000001', $overlayLink->attr('href'));
        $this->assertStringStartsWith('30 days left to watch', $overlayLink->attr('title'));
        $this->assertEquals('programmeobjectlink=cta', $overlayLink->attr('data-linktrack'));
        // overlay link icon
        $iconContainer = $overlayLink->filter('.cta__icon');
        $this->assertCount(1, $iconContainer);
        $this->assertHasClasses('cta__icon iplayer-icon cta__with-link', $iconContainer, 'Icon container classes');
        $this->assertCount(1, $iconContainer->filter('svg.gelicon'), 'svg icon present');

        // Test main link target
        $targetLink = $crawler->filter('.block-link__target');
        $this->assertEquals('http://localhost/programmes/p0000001', $targetLink->attr('href'), 'Main block-link target URL');
        $this->assertEquals('programmeobjectlink=title', $targetLink->attr('data-linktrack'), 'Main block link tracking');

        // Test titles
        $h4 = $crawler->filter('h4.programme__titles');
        $this->assertCount(1, $h4, 'programme__titles present');

        $mainTitle = $h4->filter('.programme__title');
        $this->assertEquals('EastEnders', $mainTitle->text(), 'Assert main title is EastEnders');

        $subTitles = $h4->filter('.programme__subtitle');
        $this->assertCount(1, $subTitles);
        $this->assertEquals('An Episode of Eastenders', $subTitles->text());

        // Synopsis and Episode x of n
        $synopsisP = $crawler->filter('.programme__synopsis');
        $this->assertCount(1, $synopsisP, 'Programme has synopsis');
        $this->assertContains('2/5', $synopsisP->text(), 'Synopsis contains x/n parent episode count');
        $this->assertEquals('Short Synopsis', trim($synopsisP->filter('span')->eq(2)->text()), 'Short synopsis is correct');
    }

    public function testBookOfTheWeekEpisode()
    {
        Chronos::setTestNow(new Chronos('2017-06-01 12:00:00'));

        $programme = EpisodesFixtures::beyondSpaceAndTimeAvailable();
        $presenterFactory = TwigEnvironmentProvider::ds2013PresenterFactory();
        $presenter = $presenterFactory->programmePresenter($programme, []);

        $crawler = $this->presenterCrawler($presenter);

        $outerDiv = $crawler->filterXPath('//div');
        // Test outer div classes
        $this->assertHasClasses('programme programme--radio programme--episode block-link', $outerDiv, 'Outer div classes');
        $this->assertEquals('b0849ccf', $outerDiv->attr('data-pid'));

        // Test image container and lazy loaded image
        $imageContainer = $crawler->filter('.programme__img');
        $this->assertHasClasses(
            'programme__img 1/4@bpb1 1/4@bpb2 1/3@bpw programme__img--available programme__img--hasimage',
            $imageContainer,
            'Image container div classes'
        );
        $imageLazy = $imageContainer->filter('.lazyload');
        $this->assertCount(1, $imageLazy);
        $this->assertContains('p04hc8d1', $imageLazy->attr('data-srcset'));

        //Test overlay link and icon
        $overlayDiv = $crawler->filter('.cta__overlay');
        $this->assertCount(1, $overlayDiv);
        $this->assertHasClasses('cta__overlay', $overlayDiv, 'Overlay container classes');

        $overlayLink = $overlayDiv->filterXPath('//a')->first();
        $this->assertEquals('http://localhost/radio/play/b0849ccf', $overlayLink->attr('href'));
        $this->assertStringStartsWith('Listen now', $overlayLink->attr('title'));
        $this->assertEquals('programmeobjectlink=cta', $overlayLink->attr('data-linktrack'));
        // overlay link icon
        $iconContainer = $overlayLink->filter('.cta__icon');
        $this->assertCount(1, $iconContainer);
        $this->assertHasClasses('cta__icon cta__with-link', $iconContainer, 'Icon container classes');
        $this->assertCount(1, $iconContainer->filter('svg.gelicon'), 'svg icon present');

        // Test main link target
        $targetLink = $crawler->filter('.block-link__target');
        $this->assertEquals('http://localhost/programmes/b0849ccf', $targetLink->attr('href'), 'Main block-link target URL');
        $this->assertEquals('programmeobjectlink=title', $targetLink->attr('data-linktrack'), 'Main block link tracking');

        // Test titles
        $h4 = $crawler->filter('h4.programme__titles');
        $this->assertCount(1, $h4, 'programme__titles present');

        $mainTitle = $h4->filter('.programme__title');
        $this->assertEquals('Book of the Week', $mainTitle->text(), 'Assert main title is Book of the Week');

        $subTitles = $h4->filter('.programme__subtitle');
        $this->assertCount(1, $subTitles, 'Programme has subtitles');

        // Make sure subtitles have correct text
        $subTitle1 = $subTitles->filter('span')->eq(1);
        $this->assertEquals('Reality Is Not What It Seems', $subTitle1->text());

        $subTitle1 = $subTitles->filter('span')->eq(2);
        $this->assertEquals('Beyond Space and Time', $subTitle1->text());

        // Synopsis and Episode x of n
        $synopsisP = $crawler->filter('.programme__synopsis');
        $this->assertCount(1, $synopsisP, 'Programme has synopsis');
        $this->assertContains('5/5', $synopsisP->text(), 'Synopsis contains x/n parent episode count');
        $this->assertEquals(
            'Carlo Rovelli\'s account of scientific discovery examines what happened before the Big Bang',
            trim($synopsisP->filter('span')->eq(2)->text()),
            'Short synopsis is correct'
        );
    }

    /**
     * @group cta__data_link_track
     * @dataProvider imageOptionsProvider
     */
    public function testDataLinkTrack(array $givenImageOptions, $expectedValueOnLinkTrack)
    {
        $programme = EpisodeBuilder::anyWithPlayableDestination()->build();

        $crawler = $this->presenterCrawler(
            TwigEnvironmentProvider::ds2013PresenterFactory()->programmePresenter($programme, $givenImageOptions)
        );

        $this->assertEquals(
            $expectedValueOnLinkTrack,
            $crawler->filter('.cta__standalone a')->attr('data-linktrack')
        );
    }

    public function imageOptionsProvider(): array
    {
        return [
            'CTA is set with default value' => [
                ['image_options' =>  ['show_standalone_cta' => true]],
                'programmeobjectlink=cta',
            ],
            'CTA is set with the specified value' => [
                ['image_options' =>  ['show_standalone_cta' => true, 'cta_link_location_track' => 'my_custom_tag']],
                'my_custom_tag',
            ],
        ];
    }

    protected function tearDown()
    {
        ApplicationTime::blank();
    }
}
