<?php
namespace Tests\App\Controller;

use Symfony\Component\DomCrawler\Crawler;
use Tests\App\BaseWebTestCase;

/**
 * @covers App\Controller\SchedulesByNetworkUrlKeyController
 */
class SchedulesByNetworkUrlKeyControllerTest extends BaseWebTestCase
{
    private $client;

    /** @var Crawler */
    private $crawler;

    public function setUp()
    {
        $this->loadFixtures(["NetworksAndServicesFixture"]);
    }

    public function testCanRedirectFromNetworkUrlKeyToDefaultPidService()
    {
        $this->userRequestUrlUsingNetworkUrlKey('/schedules/network/bbcone');

        $this->assertResponseStatusCode($this->client, 301);
        $this->assertEquals('/schedules/p00fzl6p', $this->client->getResponse()->headers->get('location'));
    }

    /**
     * @dataProvider networkUrlKeyProvider
     */
    public function test404ResponseIsGivenForInvalidUrlKeyNetwork($providedUrlKey)
    {
        $this->userRequestUrlUsingNetworkUrlKey('/schedules/network/' . $providedUrlKey);

        $this->assertResponseStatusCode($this->client, 404);
        $this->assertEquals('Network not found', $this->crawler->filter('.exception-message-wrapper h1')->text());
    }

    /**
     * @return string[]
     */
    public function networkUrlKeyProvider(): array
    {
        return [
            'CASE 1: network with specified url_key doesnt exist' => ['wrongurlkey'],
            'CASE 2: network with specified url_key has not default service' => ['bbcsix'],
        ];
    }

    private function userRequestUrlUsingNetworkUrlKey($url): void
    {
        $this->client = static::createClient();
        $this->crawler = $this->client->request('GET', $url);
    }
}
