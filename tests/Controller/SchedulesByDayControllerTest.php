<?php
declare(strict_types = 1);
namespace Tests\App\Controller;

use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use Cake\Chronos\Chronos;
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
            ApplicationTime::setTime((new Chronos($timeNow))->timestamp);
        }

        $this->loadFixtures(["BroadcastsFixture"]);

        $client = static::createClient();
        $url = '/schedules/' . $network;
        if (!is_null($scheduleDate)) {
            $url .= '/' . $scheduleDate;
        }
        $crawler = $client->request('GET', $url);

        $this->assertResponseStatusCode($client, 200);
        $broadcasts = $crawler->filter("meta[property='endDate']")->extract(['content']);
        $this->assertEquals($expectedBroadcasts, $broadcasts);
        $this->assertHasRequiredResponseHeaders($client);
    }

    public function scheduleDateTestProvider(): array
    {
        return [
            'radio-no-date' => ['2017-05-22 00:00:00', 'p00fzl8v', null, ['2017-05-22T03:45:00+01:00', '2017-05-22T15:00:00+01:00', '2017-05-22T15:45:00+01:00', '2017-05-23T03:00:00+01:00', '2017-05-23T03:45:00+01:00']],
            'radio-with-date' => [null, 'p00fzl8v', '2017/05/22', ['2017-05-22T03:45:00+01:00', '2017-05-22T15:00:00+01:00', '2017-05-22T15:45:00+01:00', '2017-05-23T03:00:00+01:00', '2017-05-23T03:45:00+01:00']],
            'tv-no-date' => ['2017-05-22 09:00:00', 'p00fzl6p', null, ['2017-05-22T15:45:00+01:00', '2017-05-23T03:00:00+01:00', '2017-05-23T03:45:00+01:00']],
            'tv-no-date-tomorrow-before-6am' => ['2017-05-23 03:00:00', 'p00fzl6p', null, ['2017-05-22T15:45:00+01:00', '2017-05-23T03:00:00+01:00', '2017-05-23T03:45:00+01:00']],
            'tv-with-date' => [null, 'p00fzl6p', '2017/05/22', ['2017-05-22T15:45:00+01:00', '2017-05-23T03:00:00+01:00', '2017-05-23T03:45:00+01:00']],
            'radio-no-date-and-utcoffset' => ['2017-05-22 00:00:00', 'p00fzl8v?utcoffset=%2B04%3A00', null, ['2017-05-22T03:45:00+01:00', '2017-05-22T15:00:00+01:00', '2017-05-22T15:45:00+01:00']],
            'radio-with-date-and-utcoffset' => [null, 'p00fzl8v', '2017/05/22?utcoffset=%2B04%3A00', ['2017-05-22T03:45:00+01:00', '2017-05-22T15:00:00+01:00', '2017-05-22T15:45:00+01:00']],
            'tv-no-date-and-utcoffset' => ['2017-05-22 09:00:00', 'p00fzl6p?utcoffset=%2B04%3A00', null, ['2017-05-22T03:45:00+01:00', '2017-05-22T15:00:00+01:00', '2017-05-22T15:45:00+01:00']],
            'tv-no-date-tomorrow-before-6am-and-utcoffset' => ['2017-05-23 03:00:00', 'p00fzl6p?utcoffset=%2B04%3A00', null, ['2017-05-23T03:45:00+01:00']],
            'tv-with-date-and-utcoffset' => [null, 'p00fzl6p', '2017/05/22?utcoffset=%2B04%3A00', ['2017-05-22T03:45:00+01:00', '2017-05-22T15:00:00+01:00', '2017-05-22T15:45:00+01:00']],
        ];
    }

    /**
     * @dataProvider scheduleDateIstatsTestProvider
     * @param string $network           The pid of the network
     * @param null|string $scheduleDate      The date the user is viewing the schedule for, can be null if $timeNow is set
     * @param string $bbcSite           iStats label
     * @param string $eventMasterBrand  iStats label
     * @param string $scheduleOffset    iStats label
     * @param string $scheduleContext   iStats label
     * @param string $scheduleFortnight iStats label
     */
    public function testSchedulesIstatsLabels(
        string $network,
        ?string $scheduleDate,
        string $bbcSite,
        string $eventMasterBrand,
        string $scheduleOffset,
        string $scheduleContext,
        string $scheduleFortnight
    ) {
        $this->loadFixtures(["BroadcastsFixture"]);

        $client = static::createClient();
        $url = '/schedules/' . $network;
        if (!is_null($scheduleDate)) {
            $url .= '/' . $scheduleDate;
        }
        $crawler = $client->request('GET', $url);

        $labels = $this->extractIstatsLabels($crawler);
        $this->assertEquals('programmes', $labels['app_name']);
        $this->assertEquals('programmes', $labels['prod_name']);
        $this->assertEquals('schedules_day', $labels['progs_page_type']);
        $this->assertEquals($bbcSite, $labels['bbc_site']);
        $this->assertEquals($eventMasterBrand, $labels['event_master_brand']);
        $this->assertEquals($scheduleOffset, $labels['schedule_offset']);
        $this->assertEquals($scheduleContext, $labels['schedule_context']);
        $this->assertEquals($scheduleFortnight, $labels['schedule_current_fortnight']);
        $this->assertTrue(is_numeric($labels['app_version']));
    }

    public function scheduleDateIstatsTestProvider(): array
    {
        $dateTomorrow = Chronos::tomorrow()->format('Y/m/d');
        $dateYesterday = Chronos::yesterday()->format('Y/m/d');
        $dateTwentyDaysAgo = Chronos::today()->addDays(-20)->format('Y/m/d');
        return [
            'radio-no-date'   => ['p00fzl8v', null, 'iplayerradio-radio2', 'bbc_radio_two', '0', 'today', 'true'],
            'radio-tomorrow'  => ['p00fzl8v', $dateTomorrow, 'iplayerradio-radio2', 'bbc_radio_two', '+1', 'future', 'true'],
            'radio-yesterday' => ['p00fzl8v', $dateYesterday, 'iplayerradio-radio2', 'bbc_radio_two', '-1', 'past', 'true'],
            'tv-with-date'    => ['p00fzl6p', $dateTwentyDaysAgo, 'tvandiplayer', 'bbc_one', '-20', 'past', 'false'],
        ];
    }

    /**
     * BroadcastDay start at 6:00, this method test schedules page before and after 6:00
     */
    public function testSchedulesIstatsLabelsAtDifferentDayTime()
    {
        // 1.- Before 6:00
        $timeZone = ApplicationTime::getLocalTimeZone();
        $timeViewing = new Chronos('05:00', $timeZone);
        ApplicationTime::setTime($timeViewing->timestamp);

        $this->loadFixtures(["BroadcastsFixture"]);

        $client = static::createClient();
        $url = '/schedules/p00fzl6p'; // TV schedule
        $crawler = $client->request('GET', $url);

        $labels = $this->extractIstatsLabels($crawler);

        $this->assertEquals('-1', $labels['schedule_offset']);
        $this->assertEquals('past', $labels['schedule_context']);

        // 2.- After 6:00
        $timeViewing = new Chronos('07:00', $timeZone);
        ApplicationTime::setTime($timeViewing->timestamp);

        $this->loadFixtures(["BroadcastsFixture"]);

        $client = static::createClient();
        $url = '/schedules/p00fzl6p'; // TV schedule
        $crawler = $client->request('GET', $url);

        $labels = $this->extractIstatsLabels($crawler);

        $this->assertEquals('0', $labels['schedule_offset']);
        $this->assertEquals('today', $labels['schedule_context']);
    }

    public function testScheduleIsNotFound()
    {
        // This empties the DB to ensure previous iterations are cleared
        $this->loadFixtures([]);

        $client = static::createClient();
        $crawler = $client->request('GET', '/schedules/p00fzl6p');

        $this->assertResponseStatusCode($client, 404);
    }

    public function testScheduleForDateIsNotFound()
    {
        $this->loadFixtures(["BroadcastsFixture"]);

        $client = static::createClient();
        $crawler = $client->request('GET', '/schedules/p00fzl6p/2017/03/04');

        $this->assertResponseStatusCode($client, 404);
    }

    public function testNoScheduleBeginsOn()
    {
        $this->loadFixtures(["BroadcastsFixture"]);

        $client = static::createClient();
        $url = '/schedules/p00rfdrb/2012/07/24';
        $crawler = $client->request('GET', $url);

        $this->assertResponseStatusCode($client, 404);
        $message = $crawler->filter(".noschedule")->text();
        $this->assertEquals('Broadcast schedule begins on Wednesday 25 July 2012', trim($message));
        $this->assertHasRequiredResponseHeaders($client);
    }

    public function testNoScheduleEndedOn()
    {
        $this->loadFixtures(["BroadcastsFixture"]);

        $client = static::createClient();
        $url = '/schedules/p00rfdrb/2012/08/15';
        $crawler = $client->request('GET', $url);

        $this->assertResponseStatusCode($client, 404);
        $message = $crawler->filter(".noschedule")->text();
        $this->assertEquals('Broadcast schedule ended on Tuesday 14 August 2012', trim($message));
        $this->assertHasRequiredResponseHeaders($client);
    }

    public function testNoScheduleNoResult()
    {
        $this->loadFixtures(["BroadcastsFixture"]);

        foreach (['2012/07/25', '2012/08/14'] as $date) {
            $client = static::createClient();
            $url = '/schedules/p00rfdrb/' . $date;
            $crawler = $client->request('GET', $url);

            $this->assertResponseStatusCode($client, 404);
            $message = $crawler->filter(".noschedule")->text();
            $this->assertEquals('There is no schedule for today. Please choose another day from the calendar', trim($message));
            $this->assertHasRequiredResponseHeaders($client);
        }
    }

    /**
     * @dataProvider invalidFormatDatesProvider
     * @dataProvider invalidDatesForControllerValidationProvider
     */
    public function testResponseIs404ForIncorrectDates(string $expectedMsgException, string $schedulesDateProvided)
    {
        $client = static::createClient();
        $url = '/schedules/p00rfdrb/' . $schedulesDateProvided;

        $crawler = $client->request('GET', $url);

        $this->assertResponseStatusCode($client, 404);
        $this->assertEquals($expectedMsgException, $crawler->filter('.exception-message-wrapper h1')->text());
    }

    public function invalidFormatDatesProvider(): array
    {
        // trigger INVALID ARGUMENT EXCEPTION (routing exception)
        return [
            'CASE 1: valid date but invalid format number' => ['No route found for "GET /schedules/p00rfdrb/2012/7/20"', '2012/7/20'],
            'CASE 2: valid date but invalid format string' => ['No route found for "GET /schedules/p00rfdrb/2012-7-20"', '2012-7-20'],
        ];
    }

    public function invalidDatesForControllerValidationProvider(): array
    {
        // trigger HTTP NOT FOUND EXCEPTION (validation exception)
        return [
            'CASE 1: nonexistent month' => ['Invalid date supplied', '2012/13/20'],
            'CASE 2: nonexistent month' => ['Invalid date supplied', '2012/00/20'],
            'CASE 3: nonexistent day' => ['Invalid date supplied', '2012/02/36'],
            'CASE 4: nonexistent day' => ['Invalid date supplied', '2009/02/00'],
            'CASE 5: invalid year, previous to 1900' => ['Invalid date supplied', '1800/02/20'],
        ];
    }

    /**
     * @dataProvider validsUtcOffsetsProvider
     */
    public function testUtcOffsetModifyTimezoneInSchedulesByDay(string $utcOffsetProvided)
    {
        $this->loadFixtures(["BroadcastsFixture"]);

        $client = static::createClient();
        $client->request('GET', '/schedules/p00fzl8v/2017/05/22?utcoffset=' . $utcOffsetProvided);

        $this->assertResponseStatusCode($client, 200);
    }

    public function validsUtcOffsetsProvider(): array
    {
        // utc offset needs the symbol +/- always
        return [
            'CASE 1: by_day utcoffset can be positive' => [urlencode('+10:00')],
            'CASE 2: by_day utcoffset can be negative' => [urlencode('-10:00')],
        ];
    }

    /**
     * @dataProvider invalidsUtcOffsetsProvider
     */
    public function testUtcOffsetThrowExceptionWhenNoValidUtcOffsetModifyTimezoneInSchedulesByDay(string $utcOffsetProvided)
    {
        $this->loadFixtures(["BroadcastsFixture"]);

        $client = static::createClient();
        $crawler = $client->request('GET', '/schedules/p00fzl8v/2017/05/22?utcoffset=' . $utcOffsetProvided);

        $this->assertResponseStatusCode($client, 404);
        $this->assertEquals('Invalid date supplied', $crawler->filter('.exception-message-wrapper h1')->text());
    }

    public function invalidsUtcOffsetsProvider(): array
    {
        return [
            'CASE 1: by_day utcoffset without symbol +/- is not allowed' => [urlencode('10:00')],
            'CASE 2: by_day utcoffset without urlencodeding is not allowed' => ['+10:00'],
            'CASE 3: by_day utcoffset before -12h is invalid' => [urlencode('-13:00')],
            'CASE 4: by_day utcoffset after +14h is invalid' => [urlencode('15:00')],
            'CASE 5: by_day utcoffset with minutes different to 00, 15, 30, 45 are invalid' => [urlencode('10:05')],
            'CASE 6: by_day utcoffset minutes are required' => [urlencode('+10')],
            'CASE 7: by_day utcoffset cannot use hours digits with one number ' => [urlencode('-9:00')],
            'CASE 8: by_day utcoffset is invalid format' => [urlencode('-13:000')],
            'CASE 9: by_day utcoffset is invalid format' => [urlencode('-+13:00')],
            'CASE 10: by_day utcoffset is invalid format' => ['-' . urlencode('+10:00')],
        ];
    }

    protected function tearDown()
    {
        ApplicationTime::setLocalTimeZone();
    }
}
