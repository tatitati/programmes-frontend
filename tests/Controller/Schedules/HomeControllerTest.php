<?php
declare(strict_types = 1);
namespace Tests\App\Controller\Schedules;

use Tests\App\BaseWebTestCase;

/**
 * @covers \App\Controller\Schedules\HomeController
 */
class HomeControllerTest extends BaseWebTestCase
{
    public function testController()
    {
        $this->loadFixtures([]);

        $client = static::createClient();
        $crawler = $client->request('GET', '/schedules');

        $this->assertResponseStatusCode($client, 200);
        $this->assertHasRequiredResponseHeaders($client);
    }
}
