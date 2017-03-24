<?php
declare(strict_types = 1);
namespace Tests\AppBundle\Controller;

use Tests\AppBundle\BaseWebTestCase;

class HomeControllerTest extends BaseWebTestCase
{
    public function testIndex()
    {
        $this->loadFixtures([]);

        $client = static::createClient();
        $crawler = $client->request('GET', '/programmes');

        $this->assertResponseStatusCode($client, 200);
    }
}
