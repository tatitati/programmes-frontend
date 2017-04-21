<?php
declare(strict_types = 1);
namespace Tests\App\Controller;

use Tests\App\BaseWebTestCase;

/**
 * @covers App\Controller\SchedulesHomeController
 */
class SchedulesHomeControllerTest extends BaseWebTestCase
{
    public function testController()
    {
        $this->loadFixtures([]);

        $client = static::createClient();
        $crawler = $client->request('GET', '/schedules');

        $this->assertResponseStatusCode($client, 200);
    }
}
