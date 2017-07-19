<?php
declare(strict_types = 1);

namespace Tests\App\Controller;

use Tests\App\BaseWebTestCase;

/**
 * @covers App\Controller\SchedulesByYearController
 */
class SchedulesByYearControllerTest extends BaseWebTestCase
{
    /**
     * @dataProvider serviceActiveTestProvider
     * @param string $scheduleYear    The year the user is viewing the schedule for
     * @param bool $serviceIsActive
     */
    public function testResponseIs404IfServiceIsNotActive(string $scheduleYear, bool $serviceIsActive)
    {
        $this->loadFixtures(["NetworksAndServicesFixture"]);

        $client = static::createClient();
        $url = '/schedules/p00rfdrb/' . $scheduleYear; //5liveolympicextra

        $client->request('GET', $url);

        if ($serviceIsActive) {
            $this->assertResponseStatusCode($client, 200);
        } else {
            $this->assertResponseStatusCode($client, 404);
        }

        $this->assertHasRequiredResponseHeaders($client);
    }

    public function serviceActiveTestProvider(): array
    {
        return [
            'not-active-in-year' => ['2011', false],
            'starts-half-way-through-year' => ['2012', true],
            'finishes-half-way-through-year' => ['2012', true],
        ];
    }

    public function testServiceIsNotFound()
    {
        // This empties the DB to ensure previous iterations are cleared
        $this->loadFixtures([]);

        $client = static::createClient();
        $client->request('GET', '/schedules/zzzzzzzz/2012');

        $this->assertResponseStatusCode($client, 404);
    }
}
