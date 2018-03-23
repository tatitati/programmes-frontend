<?php
declare(strict_types = 1);
namespace Tests\App\Ds2013\Presenters\Domain\CoreEntity\Group;

use Tests\App\BaseTemplateTestCase;
use Tests\App\DataFixtures\PagesService\GalleriesFixtures;
use Tests\App\TwigEnvironmentProvider;

class GroupTemplateTest extends BaseTemplateTestCase
{
    public function testEastendersGallery()
    {
        $group = GalleriesFixtures::eastenders();
        $presenterFactory = TwigEnvironmentProvider::ds2013PresenterFactory();
        $presenter = $presenterFactory->groupPresenter($group, []);
        $crawler = $this->presenterCrawler($presenter);

        // Test image container and lazy loaded image
        $imageContainer = $crawler->filter('.programme__img');
        $this->assertHasClasses(
            'programme__img 1/4@bpb1 1/4@bpb2 1/3@bpw programme__img--hasimage',
            $imageContainer,
            'Image container div classes'
        );
        $imageLazy = $imageContainer->filter('.lazyload');
        $this->assertCount(1, $imageLazy);
        $this->assertContains('p01vg679', $imageLazy->attr('data-srcset'));

        // Test overlay link and icon
        $overlayDiv = $crawler->filter('.programme__overlay');
        $this->assertCount(1, $overlayDiv);
        $this->assertHasClasses('programme__overlay', $overlayDiv, 'Overlay container classes');


        // Test main link target
        $targetLink = $crawler->filter('.block-link__target');
        $this->assertEquals('http://localhost/programmes/p0000001', $targetLink->attr('href'), 'Main block-link target URL');

        // Test titles
        $h4 = $crawler->filter('h4.programme__titles');
        $this->assertCount(1, $h4, 'programme__titles present');

        $mainTitle = $h4->filter('.programme__title');
        $this->assertEquals('A Gallery of Eastenders', $mainTitle->text(), 'Assert main title is A Gallery of Eastenders');

        $subTitles = $h4->filter('.programme__subtitle');
        $this->assertCount(1, $subTitles);
        $this->assertEquals('EastEnders', $subTitles->text());
    }
}
