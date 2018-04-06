<?php
declare(strict_types=1);

namespace Tests\App\DsAmen\Presenters\Domain\CoreEntity\Programme;

use App\Builders\EpisodeBuilder;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use Cake\Chronos\Chronos;
use Tests\App\BaseTemplateTestCase;
use Tests\App\DataFixtures\PagesService\ClipsFixture;
use Tests\App\TwigEnvironmentProvider;

class ProgrammeTemplateTest extends BaseTemplateTestCase
{
    public function setUp()
    {
        $now = new Chronos('2017-06-01 12:00:00');
        ApplicationTime::setTime($now->getTimestamp());
    }

    public function testEpisodeWithCta()
    {
        $programme = ClipsFixture::eastendersAvailable();
        $presenterFactory = TwigEnvironmentProvider::dsAmenPresenterFactory();
        $presenter = $presenterFactory->programmePresenter($programme, ['image_options' => ['badge_text' => 'New!']]);
        $crawler = $this->presenterCrawler($presenter);

        $outerDiv = $crawler->children()->children(); // skip html and body tag
        $this->assertHasClasses('media box-link gel-long-primer media--column media--card', $outerDiv, 'Outer div classes');
        $this->assertStringStartsWith('ds-amen-ProgrammePresenter-', $outerDiv->attr('id'));
        $this->assertSame(2, $outerDiv->children()->count());

        /* Programme Item Body */
        $body = $outerDiv->children()->eq(0);
        $this->assertHasClasses('media__body', $body, 'Body Class');

        $mediaDetails = $body->children();
        $this->assertSame(1, $mediaDetails->count());
        $this->assertHasClasses('media__details br-box-subtle', $mediaDetails, 'Media Body classes');

        $detailsWrapper = $mediaDetails->children();
        $this->assertSame(1, $detailsWrapper->count());
        $this->assertHasClasses('media__meta-group', $detailsWrapper, 'Details wrapper classes');

        $detailsLink = $detailsWrapper->children();
        $this->assertSame(1, $detailsLink->count());
        $this->assertSame('a', $detailsLink->nodeName());
        $this->assertHasClasses('box-link__target link--block br-subtle-text-ontext', $detailsLink, 'Details link classes');
        $this->assertSame('http://localhost/programmes/clp00001', $detailsLink->attr('href'));
        $this->assertSame('programmeobject_epblock', $detailsLink->attr('data-linktrack'));

        $titleWrapper = $detailsLink->children();
        $this->assertSame(1, $titleWrapper->count());
        $this->assertHasClasses('media-title', $titleWrapper, 'Title wrapper classes');

        /* Title and Subtitle */
        $titleElements = $titleWrapper->children();
        $this->assertSame(3, $titleElements->count());
        $this->assertHasClasses('media__meta-row gel-pica-bold', $titleElements->eq(0), 'Title classes');
        $this->assertSame('Available Eastenders Clip', $titleElements->eq(0)->text());
        $this->assertHasClasses('invisible', $titleElements->eq(1), 'Divisor class');
        $this->assertSame('—', $titleElements->eq(1)->text());
        $this->assertHasClasses('media__meta-row gel-pica', $titleElements->eq(2), 'Subtitle classes');
        $this->assertSame('EastEnders, An Episode of Eastenders', $titleElements->eq(2)->text());

        /* Programme Item image */
        $imageContainer = $outerDiv->children()->eq(1);
        $this->assertHasClasses('media__panel 1/1', $imageContainer, 'Image classes');

        $overlayContainer = $imageContainer->children();
        $this->assertSame(1, $overlayContainer->count());
        $this->assertHasClasses('media__overlay-container ratiobox', $overlayContainer, 'Overlay class');

        $this->assertSame(3, $overlayContainer->children()->count());

        $image = $overlayContainer->children()->eq(0);
        $this->assertHasClasses('image lazyload', $image, 'Lazyloaded image classes');

        $badge = $overlayContainer->children()->eq(1);
        $this->assertHasClasses(
            'media__overlay media__overlay--top media__overlay--inline gel-minion-bold br-box-highlight',
            $badge,
            'Badge classes'
        );
        $this->assertSame('New!', $badge->text());

        $overlay = $overlayContainer->children()->eq(2);
        $this->assertHasClasses('media__overlay media__overlay--bottom', $overlay, 'Overlay classes with badge');

        /* Icon link */
        $iconLink = $overlay->children();
        $this->assertSame(1, $iconLink->count());
        $this->assertHasClasses('icon-link cta--dark', $iconLink, 'Icon link classes');

        $linkComplex = $iconLink->children();
        $this->assertSame(1, $linkComplex->count());
        $this->assertSame('a', $linkComplex->nodeName());
        $this->assertHasClasses('link-complex', $linkComplex, 'Icon Link classes');
        $this->assertSame('programmeobject_calltoaction', $linkComplex->attr('data-linktrack'));
        $this->assertSame('http://localhost/programmes/clp00001', $linkComplex->attr('href'));
        $this->assertNull($linkComplex->attr('aria-label'));
        $this->assertEquals("-1", $linkComplex->attr('tabindex'));

        $iconCta = $linkComplex->children();
        $this->assertSame(1, $iconCta->count());
        $this->assertHasClasses('icon icon-cta icon--play', $iconCta, 'Icon CTA classes');

        $iconElements = $iconCta->children();
        $this->assertSame(2, $iconElements->count());

        $this->assertHasClasses('gelicon gelicon--programme', $iconElements->eq(0), 'Gelicon classes');
        $this->assertSame(
            '#gelicon--audio-visual--play',
            $iconElements->eq(0)->children()->first()->attr('xlink:href'),
            'SVG use statemente'
        );

        /* Duration */
        $iconLabel = $iconElements->eq(1);
        $this->assertHasClasses('icon-label gel-brevier', $iconLabel, 'Icon Label');
        $this->assertSame(2, $iconLabel->children()->count());
        $this->assertHasClasses('invisible', $iconLabel->children()->eq(0), 'Invisible span');
        $this->assertSame('Duration: ', $iconLabel->children()->eq(0)->text());
        $this->assertHasClasses('duration speak-duration', $iconLabel->children()->eq(1), 'Duration Classes');
        $this->assertSame('30 minutes', $iconLabel->children()->eq(1)->attr('aria-label'));
        $this->assertSame('30:00', $iconLabel->children()->eq(1)->text());
    }

    public function testEpisodeWithStandaloneCta()
    {
        $programme = ClipsFixture::eastendersAvailable();
        $presenterFactory = TwigEnvironmentProvider::dsAmenPresenterFactory();
        $presenter = $presenterFactory->programmePresenter($programme, ['show_image' => false]);
        $crawler = $this->presenterCrawler($presenter);

        $outerDiv = $crawler->children()->children();
        $this->assertHasClasses('media box-link gel-long-primer media--column media--card', $outerDiv, 'Outer div classes');
        $this->assertStringStartsWith('ds-amen-ProgrammePresenter-', $outerDiv->attr('id'));
        $this->assertSame(1, $outerDiv->children()->count(), 'Only child is body');

        $body = $outerDiv->children()->eq(0);
        $this->assertHasClasses('media__body', $body, 'Body Class');

        $mediaDetails = $body->children();
        $this->assertSame(1, $mediaDetails->count());
        $this->assertHasClasses('media__details media__details--noimage br-box-subtle', $mediaDetails, 'Media Body classes');
        $this->assertSame(2, $mediaDetails->children()->count(), 'Title and CTA are the only children');

        $detailsWrapper = $mediaDetails->children()->eq(0);
        $this->assertSame(1, $detailsWrapper->count());
        $this->assertHasClasses('media__meta-group', $detailsWrapper, 'Details wrapper classes');

        $detailsLink = $detailsWrapper->children();
        $this->assertSame(1, $detailsLink->count());
        $this->assertSame('a', $detailsLink->nodeName());
        $this->assertHasClasses('box-link__target link--block br-subtle-text-ontext', $detailsLink, 'Details link classes');
        $this->assertSame('http://localhost/programmes/clp00001', $detailsLink->attr('href'));
        $this->assertSame('programmeobject_epblock', $detailsLink->attr('data-linktrack'));

        $titleWrapper = $detailsLink->children();
        $this->assertSame(1, $titleWrapper->count());
        $this->assertHasClasses('media-title', $titleWrapper, 'Title wrapper classes');

        /* Title and Subtitle */
        $titleElements = $titleWrapper->children();
        $this->assertSame(3, $titleElements->count());
        $this->assertHasClasses('media__meta-row gel-pica-bold', $titleElements->eq(0), 'Title classes');
        $this->assertSame('Available Eastenders Clip', $titleElements->eq(0)->text());
        $this->assertHasClasses('invisible', $titleElements->eq(1), 'Divisor class');
        $this->assertSame('—', $titleElements->eq(1)->text());
        $this->assertHasClasses('media__meta-row gel-pica', $titleElements->eq(2), 'Subtitle classes');
        $this->assertSame('EastEnders, An Episode of Eastenders', $titleElements->eq(2)->text());

        /* CTA */
        /* Icon link */
        $iconLink = $mediaDetails->children()->eq(1);
        $this->assertSame(1, $iconLink->count());
        $this->assertHasClasses('icon-link cta--dark', $iconLink, 'Icon link classes');

        $linkComplex = $iconLink->children();
        $this->assertSame(1, $linkComplex->count());
        $this->assertSame('a', $linkComplex->nodeName());
        $this->assertHasClasses('link-complex', $linkComplex, 'Icon Link classes');
        $this->assertSame('programmeobject_calltoaction', $linkComplex->attr('data-linktrack'));
        $this->assertSame('http://localhost/programmes/clp00001', $linkComplex->attr('href'));
        $this->assertNull($linkComplex->attr('aria-label'));
        $this->assertEquals("-1", $linkComplex->attr('tabindex'));

        $iconCta = $linkComplex->children();
        $this->assertSame(1, $iconCta->count());
        $this->assertHasClasses('icon icon-cta icon--play', $iconCta, 'Icon CTA classes');

        $iconElements = $iconCta->children();
        $this->assertSame(2, $iconElements->count());

        $this->assertHasClasses('gelicon gelicon--programme', $iconElements->eq(0), 'Gelicon classes');
        $this->assertSame(
            '#gelicon--audio-visual--play',
            $iconElements->eq(0)->children()->first()->attr('xlink:href'),
            'SVG use statemente'
        );

        /* Duration */
        $iconLabel = $iconElements->eq(1);
        $this->assertHasClasses('icon-label gel-brevier', $iconLabel, 'Icon Label');
        $this->assertSame(2, $iconLabel->children()->count());
        $this->assertHasClasses('invisible', $iconLabel->children()->eq(0), 'Invisible span');
        $this->assertSame('Duration: ', $iconLabel->children()->eq(0)->text());
        $this->assertHasClasses('duration speak-duration', $iconLabel->children()->eq(1), 'Duration Classes');
        $this->assertSame('30 minutes', $iconLabel->children()->eq(1)->attr('aria-label'));
        $this->assertSame('30:00', $iconLabel->children()->eq(1)->text());
    }

    /**
     * @dataProvider mediaTypeProvider
     */
    public function testEpisodeHandleTheDisplayOfDuration($programmeBuilder, $mediaType, $expectDurationBeingDisplayed)
    {
        $episode = $programmeBuilder->with(['mediaType' => $mediaType])->build();

        $presenterFactory = TwigEnvironmentProvider::dsAmenPresenterFactory();
        $presenter = $presenterFactory->programmePresenter($episode, ['show_image' => false]);

        $crawler = $this->presenterCrawler($presenter);

        $isDurationFound = $crawler->filter('.duration')->count() > 0 ?: false;

        $this->assertSame($expectDurationBeingDisplayed, $isDurationFound);
    }

    public function mediaTypeProvider()
    {
        $programmeRadio = EpisodeBuilder::anyRadioEpisode()->with(['isStreamable' => true]);
        $programmeTv = EpisodeBuilder::anyTVEpisode()->with(['isStreamable' => true]);

        return [
            [$programmeRadio, 'audio', false],
            [$programmeRadio, 'audio_video', false],
            [$programmeRadio, '', false],

            [$programmeTv, 'audio', true],
            [$programmeTv, 'audio_video', false],
            [$programmeTv, '', true],
        ];
    }

    protected function tearDown()
    {
        ApplicationTime::blank();
    }
}
