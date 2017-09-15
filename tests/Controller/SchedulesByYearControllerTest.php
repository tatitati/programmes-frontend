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
    public function testResponseIs404IfServiceIsNotActive(string $scheduleYear, int $expectedResponseCode)
    {
        $this->loadFixtures(["NetworksAndServicesFixture"]);

        $client = static::createClient();
        $url = '/schedules/p00rfdrb/' . $scheduleYear; //5liveolympicextra

        $client->request('GET', $url);

        $this->assertResponseStatusCode($client, $expectedResponseCode);
        $this->assertHasRequiredResponseHeaders($client);
    }

    public function serviceActiveTestProvider(): array
    {
        return [
            'SERVICE IS ACTIVE: not-active-in-year' => ['2011', 404],
            'SERVICE IS NOT ACTIVE: starts-half-way-through-year' => ['2012', 200],
            'SERVICE IS NOT ACTIVE: finishes-half-way-through-year' => ['2012', 200],
        ];
    }

    public function testResponseIs404FromRoutingForYearBefore1900()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/schedules/p00rfdrb/' . 1800);

        $this->assertResponseStatusCode($client, 404);
        $this->assertEquals('Invalid date supplied', $crawler->filter('.exception-message-wrapper h1')->text());
    }

    public function testServiceIsNotFound()
    {
        // This empties the DB to ensure previous iterations are cleared
        $this->loadFixtures([]);

        $client = static::createClient();
        $client->request('GET', '/schedules/zzzzzzzz/2012');

        $this->assertResponseStatusCode($client, 404);
    }

    /**
     * @dataProvider serviceActiveTestProvider
     */
    public function testSchedulesByYearIstatsLabels(string $scheduleDate)
    {
        $this->loadFixtures(["NetworksAndServicesFixture"]);

        $client = static::createClient();
        $url = '/schedules/p00rfdrb/' . $scheduleDate;

        $crawler = $client->request('GET', $url);

        $labels = $this->extractIstatsLabels($crawler);
        $this->assertEquals('schedules_year', $labels['progs_page_type']);
        $this->assertEquals('iplayerradio', $labels['bbc_site']);
        $this->assertEquals('bbc_radio_five_live_olympics_extra', $labels['event_master_brand']);
        $this->assertTrue(is_numeric($labels['app_version']));
    }
}
