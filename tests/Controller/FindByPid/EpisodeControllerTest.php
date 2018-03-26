<?php
namespace Tests\App\Controller\FindByPid;

use Symfony\Bundle\FrameworkBundle\Client;
use Tests\App\BaseWebTestCase;

class EpisodeControllerTest extends BaseWebTestCase
{
    /** @var Client */
    protected $client;

    public function setUp()
    {
        $this->loadFixtures([
            'CollapsedBroadcastsFixture',
        ]);
        $this->client = static::createClient();
    }

    /**
     * [more panel] is displayed
     */
    public function testLastPanelHasSpecialCssClass()
    {
        $episodeWithOnlyMorePanel = 'p3000004';

        // selectors for side map section
        $selectorSideMap = ".map--episode .map__column--last";
        $selectorSidePanels = "$selectorSideMap .map__inner";

        // selectors for panel in side sections
        $wantedClass = '.fauxcolumn';
        $unwantedClass = ".map__column";

        $crawler = $this->client->request('GET', '/programmes/' . $episodeWithOnlyMorePanel);

        $this->assertResponseStatusCode($this->client, 200);


        $this->assertEquals(1, $crawler->filter($selectorSidePanels)->count());
        $this->assertEquals(1, $crawler->filter($selectorSidePanels . ' ' . $wantedClass)->count());
        $this->assertEquals(0, $crawler->filter($selectorSidePanels . ' ' . $unwantedClass)->count());
    }

    /**
     * [tx panel + empty panel]
     */
    public function testLastPanelHasTwoSpecialCssClasses()
    {
        $episodeWithUpcomingBroadcasts = 'p3000002';

        $selectorSideMap = ".map--episode .map__column--last .br-box-secondary";
        $selectorSideMapLastPanel = ".map--episode .map__column--last .fauxcolumn.map__column";

        $this->client = static::createClient();
        $crawler = $this->client->request('GET', '/programmes/' . $episodeWithUpcomingBroadcasts);

        $this->assertResponseStatusCode($this->client, 200);
        $this->assertEquals(2, $crawler->filter($selectorSideMap)->count());
        $this->assertEquals(1, $crawler->filter($selectorSideMapLastPanel)->count());
    }

    public function testBroadcastListShowThreeBroadcastsAndHidesTheRest()
    {
        $this->client = static::createClient();
        $crawler = $this->client->request('GET', '/programmes/p3000002'); // 4 broadcasts

        $this->assertResponseStatusCode($this->client, 200);
        $this->assertEquals(4, $crawler->filter('#broadcasts li')->count()); // All
        $this->assertEquals(1, $crawler->filter('#broadcasts li.ml__hidden')->count()); // Hidden
        $this->assertEquals(1, $crawler->filter('#broadcasts .ml__button')->count()); // Show/Hide Button
    }
}
