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
        // trigger INVALID ARGUMENT EXCEPTION
        return [
            'CASE 1: nonexistent week' => ['No route found for "GET /schedules/p00rfdrb/2012/w54"', '2012/w54'],
            'CASE 3: valid week but invalid format number' => ['No route found for "GET /schedules/p00rfdrb/2012/w7"', '2012/w7'],
            'CASE 4: valid week but invalid format string' => ['No route found for "GET /schedules/p00rfdrb/2012-w7"', '2012-w7'],
        ];
    }

    public function invalidDatesForControllerValidationProvider(): array
    {
        // trigger HTTP NOT FOUND EXCEPTION
        return [
            'CASE 2: nonexistent week' => ['Invalid date supplied', '2012/w00'],
        ];
    }
}
