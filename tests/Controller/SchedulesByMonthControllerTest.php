<?php
declare(strict_types = 1);

namespace Tests\App\Controller;

use Tests\App\BaseWebTestCase;

/**
 * @covers App\Controller\SchedulesByMonthController
 */
class SchedulesByMonthControllerTest extends BaseWebTestCase
{
    /**
     * @dataProvider serviceActiveTestProvider
     * @param string $scheduleDate    The month the user is viewing the schedule for
     * @param bool $serviceIsActive
     */
    public function testResponseIs404IfServiceIsNotActive(string $scheduleDate, bool $serviceIsActive)
    {
        $this->loadFixtures(["NetworksAndServicesFixture"]);

        $client = static::createClient();
        $url = '/schedules/p00rfdrb/' . $scheduleDate; //5liveolympicextra

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
            'not-active-in-month' => ['2012/06', false],
            'starts-half-way-through-month' => ['2012/07', true],
            'finishes-half-way-through-month' => ['2012/08', true],
        ];
    }

    public function testServiceIsNotFound()
    {
        // This empties the DB to ensure previous iterations are cleared
        $this->loadFixtures([]);

        $client = static::createClient();
        $client->request('GET', '/schedules/zzzzzzzz/2012/12');

        $this->assertResponseStatusCode($client, 404);
    }

    /**
     * @dataProvider serviceActiveTestProvider
     */
    public function testSchedulesByMonthIstatsLabels(string $scheduleDate)
    {
        $this->loadFixtures(["NetworksAndServicesFixture"]);

        $client = static::createClient();
        $url = '/schedules/p00rfdrb/' . $scheduleDate;

        $crawler = $client->request('GET', $url);

        $labels = $this->extractIstatsLabels($crawler);
        $this->assertEquals('schedules_month', $labels['progs_page_type']);
        $this->assertEquals('iplayerradio', $labels['bbc_site']);
        $this->assertEquals('bbc_radio_five_live_olympics_extra', $labels['event_master_brand']);
        $this->assertTrue(is_numeric($labels['app_version']));
    }
}
