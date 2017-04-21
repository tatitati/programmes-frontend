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
        $this->loadFixtures([]);

        $client = static::createClient();
        $crawler = $client->request('GET', '/programmes');

        $this->assertResponseStatusCode($client, 200);
    }
}
