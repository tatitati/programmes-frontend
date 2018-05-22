<?php
namespace Tests\App\Controller\FindByPid\EpisodeController;

use App\Controller\FindByPid\EpisodeController;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Tests\App\BaseWebTestCase;

/**
 * @covers EpisodeController
 * @group podcast
 *
 * It makes sure that podcast panel section appear properly in the episode page.
 */
class EpisodeControllerPodcastTest extends BaseWebTestCase
{
    /** @var Client */
    protected $client;

    /** @var Crawler */
    private $crawler;

    public function setUp()
    {
        $this->loadFixtures([
            'ProgrammeEpisodes\EpisodesFixtures',
        ]);
        $this->client = static::createClient();
    }

    public function testPodcastPanelIsUsingCorrectUrls()
    {
        $this->userVisitEpisode("programmes/p3000000");

        $expectedTleoPid = 'b006q2x0';
        $this->thenLazyContentIsLoadedFrom("/programmes/$expectedTleoPid/podcasts.2013inc");
        $this->thenPanelWithoutJSTakesUserTo("/programmes/$expectedTleoPid/episodes/downloads");
        $this->thenDownloadButtonDisplayText('Download (UK Only)');
    }

    public function testPodcastPanelIsNotLoadedForEpisodesWithNoPodcastableVersions()
    {
        $this->userVisitEpisode("programmes/b000sr21");

        $this->thenNoPodcastPanelIsDisplayed();
    }

    /**
     * Helpers
     */
    private function thenLazyContentIsLoadedFrom(string $expectedUrl)
    {
        $lazyUrlSelector = '#podcast .lazy-module';
        $lazyUrl = $this->crawler->filter($lazyUrlSelector)->attr('data-lazyload-inc');

        $this->assertEquals($expectedUrl, $lazyUrl);
    }

    private function thenPanelWithoutJSTakesUserTo(string $expectedUrl)
    {
        $lazyUrlSelector = '#podcast .lazy-module .br-box-page__link';
        $lazyUrl = $this->crawler->filter($lazyUrlSelector)->attr('href');

        $this->assertEquals($expectedUrl, $lazyUrl);
    }

    private function userVisitEpisode(string $url)
    {
        $crawler = $this->client->request('GET', $url);
        $this->assertResponseStatusCode($this->client, 200);
        $this->crawler = $crawler;
    }

    private function thenNoPodcastPanelIsDisplayed()
    {
        $this->assertSame(0, $this->crawler->filter('#podcast')->count());
    }

    private function thenDownloadButtonDisplayText($expectedText)
    {
        $this->assertEquals(
            $expectedText,
            trim($this->crawler->filter('.episode-panel__intro .popup__button label')->text())
        );
    }
}
