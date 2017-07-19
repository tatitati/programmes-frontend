<?php
declare(strict_types = 1);
namespace Tests\App\Controller;

use Tests\App\BaseWebTestCase;

/**
 * @covers App\Controller\HomeController
 */
class HomeControllerTest extends BaseWebTestCase
{
    public function testController()
    {
        $this->loadFixtures(['HomeFixture']);

        $client = static::createClient();
        $crawler = $client->request('GET', '/programmes');

        $this->assertResponseStatusCode($client, 200);

        // Programme count
        $this->assertEquals('2', $crawler->filter('span[data-count]')->text());

        // TV Networks
        $this->assertEquals(
            [['BBC One', '/schedules/p00fzl6p']],
            $crawler->filter('[data-list="tv-networks"] a')->extract(['_text', 'href'])
        );

        // National Radio Networks
        $this->assertEquals(
            [['5 live Olympics Extra', '/schedules/p00rfdrb'], ['Radio 2', '/schedules/p00fzl8v']],
            $crawler->filter('[data-list="national-radio-networks"] a')->extract(['_text', 'href'])
        );

        // Regional Radio Networks
        $this->assertEquals(
            [['Radio Cymru', '/schedules/p00fzl7b']],
            $crawler->filter('[data-list="regional-radio-networks"] a')->extract(['_text', 'href'])
        );

        // Local Radio Networks
        $this->assertEquals(
            [['Radio Berkshire', '/schedules/p00fzl74']],
            $crawler->filter('[data-list="local-radio-networks"] a')->extract(['_text', 'href'])
        );

        $this->assertHasRequiredResponseHeaders($client);
    }
}
