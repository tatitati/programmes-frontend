<?php
namespace Tests\App\Controller;

use Symfony\Component\DomCrawler\Crawler;
use Tests\App\BaseWebTestCase;

/**
 * @covers \App\Controller\SchedulesByNetworkUrlKeyController
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

    /**
     * @dataProvider validNetworkUrlKeyProvider
     */
    public function testCanRedirectFromNetworkUrlKeyToDefaultPidService($expectedRedirectUrl, $userUrlInput)
    {
        $this->userRequestUrlUsingNetworkUrlKey($userUrlInput);

        $this->assertResponseStatusCode($this->client, 301);
        $this->assertEquals($expectedRedirectUrl, $this->client->getResponse()->headers->get('location'));
    }

    public function validNetworkUrlKeyProvider()
    {
        return [
            // redirected url, user requests url
            'CASE 1: url key with only letters' => ['/schedules/p00fzl6p', '/schedules/network/bbcone'],
            'CASE 2: url key can contains digits' => ['/schedules/p00fzl8v', '/schedules/network/radio2'],
        ];
    }

    /**
     * @dataProvider invalidNetworkUrlKeyProvider
     */
    public function test404ResponseIsGivenForInvalidUrlKeyNetwork($userUrlInput)
    {
        $this->userRequestUrlUsingNetworkUrlKey($userUrlInput);

        $this->assertResponseStatusCode($this->client, 404);
        $this->assertEquals('Network not found', $this->crawler->filter('.exception-message-wrapper h1')->text());
    }

    /**
     * @return string[][]
     */
    public function invalidNetworkUrlKeyProvider(): array
    {
        return [
            // user input url
            'CASE 1: network with specified url_key doesnt exist' => ['/schedules/network/wrongurlkey'],
            'CASE 2: network with specified url_key has not default service' => ['/schedules/network/bbcsix'],
        ];
    }

    private function userRequestUrlUsingNetworkUrlKey($url): void
    {
        $this->client = static::createClient();
        $this->crawler = $this->client->request('GET', $url);
    }
}
