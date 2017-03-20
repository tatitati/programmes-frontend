<?php
declare(strict_types=1);
namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/programmes');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
