<?php
declare(strict_types = 1);

namespace Tests\App\Controller;

use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use Cake\Chronos\Chronos;
use Tests\App\BaseWebTestCase;

/**
 * @covers \App\Controller\SchedulesByWeekController
 */
class SchedulesByWeekControllerTest extends BaseWebTestCase
{
    /**
     * @dataProvider invalidFormatDatesProvider
     * @dataProvider invalidDatesForControllerValidationProvider
     */
    public function testResponseIs404FromRoutingForInvalidDates(string $expectedMsgException, string $schedulesDateProvided)
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/schedules/p00rfdrb/' . $schedulesDateProvided);

        $this->assertResponseStatusCode($client, 404);
        $this->assertEquals($expectedMsgException, $crawler->filter('.exception-message-wrapper h1')->text());
    }

    public function invalidFormatDatesProvider(): array
    {
        // trigger INVALID ARGUMENT EXCEPTION (routing exception)
        return [
            'CASE 1: nonexistent week' => ['No route found for "GET /schedules/p00rfdrb/2012/w54"', '2012/w54'],
            'CASE 2: valid week but invalid format number' => ['No route found for "GET /schedules/p00rfdrb/2012/w7"', '2012/w7'],
            'CASE 3: valid week but invalid format string' => ['No route found for "GET /schedules/p00rfdrb/2012-w7"', '2012-w7'],
            'CASE 4: valid route but invalid week'         => ['Invalid date', '2017/w53'],
        ];
    }

    public function invalidDatesForControllerValidationProvider(): array
    {
        // trigger HTTP NOT FOUND EXCEPTION (validation exception)
        return [
            'CASE 1: valid week but invalid year' => ['Invalid date supplied', '1800/w02'],
            'CASE 2: nonexistent week' => ['Invalid date supplied', '2012/w00'],
        ];
    }

    /**
     * @dataProvider validsUtcOffsetsProvider
     */
    public function testUtcOffsetModifyTimezoneInSchedulesByWeek(string $utcOffsetProvided)
    {
        $client = static::createClient();
        $client->request('GET', '/schedules/p00fzl8v/2001/w22?utcoffset=' . $utcOffsetProvided);

        $this->assertResponseStatusCode($client, 200);
    }

    public function validsUtcOffsetsProvider(): array
    {
        return [
            'CASE 1: by_week utcoffset can be positive' => [urlencode('+10:00')],
            'CASE 2: by_week utcoffset can be negative' => [urlencode('-10:00')],
        ];
    }

    /**
     * @dataProvider invalidsUtcOffsetsProvider
     */
    public function testUtcOffsetThrowExceptionWhenNoValidUtcOffsetModifyTimezoneInSchedulesByWeek(string $utcOffsetProvided)
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/schedules/p00fzl8v/2001/w22?utcoffset=' . $utcOffsetProvided);

        $this->assertResponseStatusCode($client, 404);
        $this->assertEquals('Invalid date supplied', $crawler->filter('.exception-message-wrapper h1')->text());
    }

    public function invalidsUtcOffsetsProvider(): array
    {
        return [
            'CASE 1: by_week utcoffset without symbol +/- is not allowed' => [urlencode('10:00')],
            'CASE 2: by_week utcoffset without urlencodeding is not allowed' => ['+10:00'],
            'CASE 3: by_week utcoffset before -12h is invalid' => [urlencode('-13:00')],
            'CASE 4: by_week utcoffset after +14h is invalid' => [urlencode('15:00')],
            'CASE 5: by_week utcoffset with minutes different to 00, 15, 30, 45 are invalid' => [urlencode('10:05')],
            'CASE 6: by_week utcoffset minutes are required' => [urlencode('+10')],
            'CASE 3: by_week utcoffset cannot use hours digits with one number ' => [urlencode('-9:00')],
        ];
    }

    /**
     * @dataProvider yearAndWeeksUrlsProvider
     */
    public function testNextWeekGivesProperNextWeekLink($currentWeek, $expectedNextWeek)
    {
        $this->loadFixtures(["BroadcastsFixture"]);

        $client = static::createClient();
        $url = '/schedules/p00fzl6p';
        $crawler = $client->request('GET', $url . $currentWeek);

        $this->assertResponseStatusCode($client, 200);
        $this->assertEquals(
            $url . $expectedNextWeek,
            $crawler->filter('a#next-week')->attr('href')
        );
    }

    public function yearAndWeeksUrlsProvider(): array
    {
        return [
            // [ current week, expected next week ]
            ['/2017/w20', '/2017/w21'],
            ['/2009/w53', '/2010/w01'],
            ['/2011/w52', '/2012/w01'],
        ];
    }

    public function testDataPageTimeIsSetProperlyInHtmlResponseForWeek()
    {
        $this->loadFixtures(["BroadcastsFixture"]);
        $client = static::createClient();

        $crawler = $client->request('GET', '/schedules/p00fzl8v/2017/w20');

        $this->assertResponseStatusCode($client, 200);
        $this->assertEquals(1, $crawler->filter('[data-page-time]')->count());
        // 1st day of 20th week = May-15 (Monday)
        $this->assertEquals('2017/05/15', $crawler->filter('[data-page-time]')->attr('data-page-time'));
    }

    public function testForInactiveServiceMetatagIsNotAdded()
    {
        $this->loadFixtures(["BroadcastsFixture"]);
        $client = static::createClient();

        $crawler = $client->request('GET', '/schedules/p00fzl8v/1980/w01');

        $this->assertResponseStatusCode($client, 404, 'Expected 404 when service is not active in specified week');
        $this->assertEquals('Broadcast schedule begins on Saturday 8 April 2000', trim($crawler->filter(".noschedule")->text()));
        $this->assertEquals(0, $crawler->filter(".week-guide__table__hour-row")->count());
        $this->assertFalse($this->isAddedMetaNoIndex($crawler), 'For 404 responses we dont set any meta tag noindex');
    }

    public function testForPastWeeksWithNoBroadcastsMetatagIsAdded()
    {
        $this->loadFixtures(["BroadcastsFixture"]);
        $client = static::createClient();

        $crawler = $client->request('GET', '/schedules/p00fzl8v/2000/w20');
        $this->assertResponseStatusCode($client, 200, "Expected 200 because the service is active the specified week, but no broadcasts were found");
        $this->assertEquals('There is no schedule for today. Please choose another day from the calendar', trim($crawler->filter(".noschedule")->text()));
        $this->assertEquals(0, $crawler->filter(".week-guide__table__hour-row")->count());
        $this->assertTrue($this->isAddedMetaNoIndex($crawler), 'Meta tag shouldnt be added because for past dates, if no broadcats are found, then is set');
    }

    public function testForFutureInsideNext35DaysWithNoBroadcastsDonIncludeMetatag()
    {
        $timeNow = '2000/05/10';
        ApplicationTime::setTime((new Chronos($timeNow))->timestamp);

        $this->loadFixtures(["BroadcastsFixture"]);
        $client = static::createClient();

        $crawler = $client->request('GET', '/schedules/p00fzl8v/2000/w20');

        $this->assertResponseStatusCode($client, 200, "Even if we havent broadcasts, in the period of +35 days we return 200");
        $this->assertEquals('There is no schedule for today. Please choose another day from the calendar', trim($crawler->filter(".noschedule")->text()));
        $this->assertEquals(0, $crawler->filter(".week-guide__table__hour-row")->count());
        $this->assertFalse($this->isAddedMetaNoIndex($crawler), 'Meta tag should be added because for past dates, if no broadcats are found, then is set');
    }

    public function testForFutureBeyond35DaysWithNoBroadcastsDonIncludeMetatag()
    {
        $timeNow = '2000/03/15';
        ApplicationTime::setTime((new Chronos($timeNow))->timestamp);

        $this->loadFixtures(["BroadcastsFixture"]);
        $client = static::createClient();

        $crawler = $client->request('GET', '/schedules/p00fzl8v/2000/w20');

        $this->assertResponseStatusCode($client, 404, "Expected 404 is beyond +35 days and there is no broadcasts");
        $this->assertEquals('There is no schedule for today. Please choose another day from the calendar', trim($crawler->filter(".noschedule")->text()));
        $this->assertEquals(0, $crawler->filter(".week-guide__table__hour-row")->count());
        $this->assertFalse($this->isAddedMetaNoIndex($crawler), 'Meta tag should be added because for past dates, if no broadcats are found, then is set');
    }

    private function isAddedMetaNoIndex($crawler): bool
    {
        return ($crawler->filter('meta[name="robots"]')->count() > 0 && $crawler->filter('meta[name="robots"]')->first()->attr('content') === 'noindex');
    }

    protected function tearDown()
    {
        ApplicationTime::blank();
    }
}
