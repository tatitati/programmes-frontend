<?php
namespace Tests\App\Controller\FindByPid\EpisodeController;

use Symfony\Bundle\FrameworkBundle\Client;
use Tests\App\BaseWebTestCase;

class EpisodeControllerBroadcastListTest extends BaseWebTestCase
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
