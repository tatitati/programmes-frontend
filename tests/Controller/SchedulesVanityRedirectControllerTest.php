<?php
declare(strict_types = 1);

namespace Tests\App\Controller;

use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use Cake\Chronos\Chronos;
use Tests\App\BaseWebTestCase;

/**
 * @covers App\Controller\SchedulesVanityRedirectController
 */
class SchedulesVanityRedirectControllerTest extends BaseWebTestCase
{
    /**
     * @dataProvider vanityTestProvider
     * @param string $vanity
     * @param string $urlSuffix
     */
    public function testUserIsRedirected(string $vanity, string $urlSuffix)
    {
        ApplicationTime::setTime((new Chronos('2017-09-27 12:00:00'))->getTimestamp());
        $this->loadFixtures(["NetworksAndServicesFixture"]);

        $client = static::createClient();
        $url = '/schedules/p00rfdrb/' . $vanity;

        $client->request('GET', $url);

        $this->assertRedirectTo($client, 302, '/schedules/p00rfdrb/' . $urlSuffix);
        $this->assertHasRequiredResponseHeaders($client);
    }

    /**
     * @return string[][]
     */
    public function vanityTestProvider(): array
    {
        return [
            'today' => ['today', '2017-09-27'],
            'tomorrow' => ['tomorrow', '2017-09-28'],
            'yesterday' => ['yesterday', '2017-09-26'],
            'this_week' => ['this_week', '2017-w39'],
            'next_week' => ['next_week', '2017-w40'],
            'last_week' => ['last_week', '2017-w38'],
        ];
    }
}
