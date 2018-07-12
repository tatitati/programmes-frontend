<?php
namespace Tests\App\Controller\FindByPid\EpisodeController;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Tests\App\BaseWebTestCase;

/**
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

    public function testPodcastApiDoesntReturnAnythingAndUkOnlyIsNotDisplayed()
    {
        $this->userVisitEpisode("programmes/p3000002");

        $this->thenNoPodcastPanelIsDisplayed();
        $this->thenDownloadButtonDisplayText('Download');
    }

    public function testPodcastPanelIsNotLoadedForEpisodesWithNoPodcastableVersions()
    {
        $this->userVisitEpisode("programmes/b000sr21");

        $this->thenNoPodcastPanelIsDisplayed();
        $this->thenDownloadButtonDoesntAppear();
    }

    /**
     * Helpers
     */
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

    private function thenDownloadButtonDoesntAppear()
    {
        $this->assertSame(
            0,
            $this->crawler->filter('.episode-panel__intro .popup__button label')->count()
        );
    }
}
