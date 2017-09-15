<?php
declare(strict_types = 1);

namespace Tests\App\Controller;

use Tests\App\BaseWebTestCase;

/**
 * @covers App\Controller\SchedulesByWeekController
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
            'CASE 3: valid week but invalid format number' => ['No route found for "GET /schedules/p00rfdrb/2012/w7"', '2012/w7'],
            'CASE 4: valid week but invalid format string' => ['No route found for "GET /schedules/p00rfdrb/2012-w7"', '2012-w7'],
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
}
