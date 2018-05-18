<?php
declare(strict_types=1);

namespace Tests\App\Controller\Podcast;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Tests\App\BaseWebTestCase;

/**
 * Test partial template. Imported by ajax from EpisodeController.
 *
 * @group podcast
 */
class PodcastDs2013ControllerTest extends BaseWebTestCase
{
    /** @var Client */
    private $client;

    /** @var Crawler */
    private $crawler;

    public function setUp()
    {
        $this->loadFixtures(['ProgrammeEpisodes\SeriesFixtures']);
        $this->client = static::createClient();
    }

    public function testPodcastFullPageBasicContent()
    {
        $this->userRequestPartial("programmes/b006q2x0/podcasts.2013inc");

        $this->assertResponseStatusCode($this->client, 200);
        $this->thenUserSeeTitle('Podcast');
        $this->thenUserSeeAShortDescription("this is a short description");
    }

    public function testPartialThrow404WhenThePidIsNotPodcast()
    {
        $this->userRequestPartial("programmes/b0000sr2/podcasts.2013inc");

        $this->assertResponseStatusCode($this->client, 404);
    }

    /**
     * Helpers
     */
    private function userRequestPartial(string $url)
    {
        $crawler = $this->client->request('GET', $url);
        $this->crawler = $crawler;
    }

    private function thenUserSeeTitle(string $expectedTitle)
    {
        $this->assertSame(
            $expectedTitle,
            $this->crawler->filter('.component__header h2')->text()
        );
    }

    private function thenUserSeeAShortDescription(string $expectedDescription)
    {
        $this->assertSame(
            $expectedDescription,
            trim($this->crawler->filter('.programme__body .programme__synopsis')->text())
        );
    }
}
