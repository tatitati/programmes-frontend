<?php
declare(strict_types = 1);
namespace Tests\App\Controller\Partial;

use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use Cake\Chronos\Chronos;
use Tests\App\BaseWebTestCase;

/**
 * @covers App\Controller\Partial\SchedulesOnNowController
 */
class SchedulesOnNowControllerTest extends BaseWebTestCase
{
    public function testOnNowReturnsPopulatedMarkupForDsAmenPartial()
    {
        ApplicationTime::setTime((new Chronos('2017-05-22 02:12:00'))->getTimestamp());
        $this->loadFixtures(["CollapsedBroadcastsFixture"]);

        $client = static::createClient();
        $url = '/schedules/network/bbcone/on-now?partial=legacy_amen';

        $crawler = $client->request('GET', $url);
        $this->assertResponseStatusCode($client, 200);

        $this->assertContains('Watch live', $crawler->filter('h2')->text());
        $this->assertContains('03:00', $crawler->filter('h3')->text());
        $this->assertContains('Early Episode', $crawler->filter('.media__meta-row')->text());
        $this->assertEquals('/programmes/p3000002', $crawler->filter('.box-link__target')->attr('href'));
    }

    public function testOnNowReturnsPopulatedMarkupForDs2013Partial()
    {
        ApplicationTime::setTime((new Chronos('2017-05-22 02:02:00'))->getTimestamp());
        $this->loadFixtures(["CollapsedBroadcastsFixture"]);

        $client = static::createClient();
        $url = '/schedules/network/bbcone/on-now?partial=legacy_2013';

        $crawler = $client->request('GET', $url);
        $this->assertResponseStatusCode($client, 200);

        $this->assertContains('Watch live', $crawler->filter('h2')->text());
        $this->assertContains('03:00', $crawler->filter('h3')->text());
        $this->assertContains('Early Episode', $crawler->filter('.programme__title')->text());
        $this->assertEquals('/programmes/p3000002', $crawler->filter('.br-blocklink__link')->attr('href'));
    }

    public function testOnNowRedirectsWithNoPartial()
    {
        ApplicationTime::setTime((new Chronos('2017-05-21 14:01:00'))->getTimestamp());
        $this->loadFixtures(["BroadcastsFixture"]);

        $client = static::createClient();
        $url = '/schedules/network/bbcone/on-now';

        $client->request('GET', $url);

        $this->assertRedirectTo($client, 302, '/programmes/p3000001');
    }

    public function testResponseIs404WithNoNetwork()
    {
        $client = static::createClient();
        $url = '/schedules/network/invalidNetworkKey/on-now';

        $crawler = $client->request('GET', $url);

        $this->assertResponseStatusCode($client, 404);
        $this->assertEquals("No network or service found from network key invalidNetworkKey", $crawler->filter('.exception-message-wrapper h1')->text());
    }

    protected function tearDown()
    {
        ApplicationTime::blank();
    }
}
