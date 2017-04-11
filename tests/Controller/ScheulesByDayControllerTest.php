<?php
declare(strict_types = 1);
namespace Tests\App\Controller;

use Tests\App\BaseWebTestCase;

class SchedulesByDayControllerTest extends BaseWebTestCase
{
    public function testScheduleForToday()
    {
        $this->loadFixtures([]);

        $client = static::createClient();
        $crawler = $client->request('GET', '/schedules/p00fzl6p');

        $this->assertResponseStatusCode($client, 200);
    }

    public function testScheduleForDate()
    {
        $this->loadFixtures([]);

        $client = static::createClient();
        $crawler = $client->request('GET', '/schedules/p00fzl6p/2017-03-04');

        $this->assertResponseStatusCode($client, 200);
    }
}
