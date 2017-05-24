<?php
declare(strict_types = 1);
namespace Tests\App\Controller;

use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use DateTimeImmutable;
use Tests\App\BaseWebTestCase;

/**
 * @covers App\Controller\SchedulesByDayController
 */
class SchedulesByDayControllerTest extends BaseWebTestCase
{
    /**
     * @dataProvider scheduleDateTestProvider
     * @param null|string $timeNow            The system time, can be null if setting $scheduleDate
     * @param string $network            The pid of the network
     * @param null|string $scheduleDate       The date the user is viewing the schedule for, can be null if $timeNow is set
     * @param string[] $expectedBroadcasts An array of expected broadcast times
     */
    public function testScheduleDisplaysCorrectBroadcastsForTime(?string $timeNow, string $network, ?string $scheduleDate, array $expectedBroadcasts)
    {
        if (!is_null($timeNow)) {
            $timestamp = (new DateTimeImmutable($timeNow))->getTimestamp();
            ApplicationTime::setTime($timestamp);
        }

        $this->loadFixtures(["BroadcastsFixture", "NetworksFixture"]);

        $client = static::createClient();
        $url = '/schedules/' . $network;
        if (!is_null($scheduleDate)) {
            $url .= '/' . $scheduleDate;
        }
        $crawler = $client->request('GET', $url);

        $this->assertResponseStatusCode($client, 200);
        $this->assertEquals(count($expectedBroadcasts), $crawler->filter('.broadcast')->count());
        $broadcasts = $crawler->filterXpath("//meta[@property='endDate']")->extract(['content']);
        foreach ($expectedBroadcasts as $expectedBroadcast) {
            $this->assertContains($expectedBroadcast, $broadcasts);
        }
    }

    public function scheduleDateTestProvider(): array
    {
        return [
            'radio-no-date' => ['2017-05-22 00:00:00', 'p00fzl8v', null, ['2017-05-22T02:45:00+00:00', '2017-05-22T14:45:00+00:00', '2017-05-23T02:45:00+00:00']],
            'radio-with-date' => [null, 'p00fzl8v', '2017-05-22', ['2017-05-22T02:45:00+00:00', '2017-05-22T14:45:00+00:00', '2017-05-23T02:45:00+00:00']],
            'tv-no-date' => ['2017-05-22 09:00:00', 'p00fzl6p', null, ['2017-05-22T14:45:00+00:00', '2017-05-23T02:45:00+00:00']],
            'tv-no-date-tomorrow-before-6am' => ['2017-05-23 03:00:00', 'p00fzl6p', null, ['2017-05-22T14:45:00+00:00', '2017-05-23T02:45:00+00:00']],
            'tv-with-date' => [null, 'p00fzl6p', '2017-05-22', ['2017-05-22T14:45:00+00:00', '2017-05-23T02:45:00+00:00']],
        ];
    }

    public function testScheduleIsNotFound()
    {
        $this->loadFixtures([]);

        $client = static::createClient();
        $crawler = $client->request('GET', '/schedules/p00fzl6p');

        $this->assertResponseStatusCode($client, 404);
    }

    public function testScheduleForDateIsNotFound()
    {
        $this->loadFixtures(["BroadcastsFixture", "NetworksFixture"]);

        $client = static::createClient();
        $crawler = $client->request('GET', '/schedules/p00fzl6p/2017-03-04');

        $this->assertResponseStatusCode($client, 404);
    }
}
