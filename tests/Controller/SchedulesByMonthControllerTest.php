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
     * @param bool   $expectedResponseCode
     */
    public function testResponseIs404IfServiceIsNotActive(string $scheduleDate, int $expectedResponseCode)
    {
        $this->loadFixtures(["NetworksAndServicesFixture"]);

        $client = static::createClient();
        $url = '/schedules/p00rfdrb/' . $scheduleDate; //5liveolympicextra

        $client->request('GET', $url);

        $this->assertResponseStatusCode($client, $expectedResponseCode);
        $this->assertHasRequiredResponseHeaders($client);
    }

    public function serviceActiveTestProvider(): array
    {
        return [
            'SERVICE IS ACTIVE: not-active-in-month' => ['2012/06', 404],
            'SERVICE IS NOT ACTIVE: starts-half-way-through-month' => ['2012/07', 200],
            'SERVICE IS NOT ACTIVE: finishes-half-way-through-month' => ['2012/08', 200],
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
