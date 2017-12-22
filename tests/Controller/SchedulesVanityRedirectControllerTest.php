<?php
declare(strict_types = 1);

namespace Tests\App\Controller;

use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use Cake\Chronos\Chronos;
use Tests\App\BaseWebTestCase;

/**
 * @covers \App\Controller\SchedulesVanityRedirectController
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

        $client->request('GET', $url . '?utcoffset=%2B01%3A00');

        $this->assertRedirectTo($client, 302, '/schedules/p00rfdrb/' . $urlSuffix . '?utcoffset=%2B01%3A00');
        $this->assertHasRequiredResponseHeaders($client);
    }

    /**
     * @return string[][]
     */
    public function vanityTestProvider(): array
    {
        return [
            'today' => ['today', '2017/09/27'],
            'tomorrow' => ['tomorrow', '2017/09/28'],
            'yesterday' => ['yesterday', '2017/09/26'],
            'this_week' => ['this_week', '2017/w39'],
            'next_week' => ['next_week', '2017/w40'],
            'last_week' => ['last_week', '2017/w38'],
            'this_month' => ['this_month', '2017/09'],
            'next_month' => ['next_month', '2017/10'],
            'last_month' => ['last_month', '2017/08'],
        ];
    }

    /**
     * @dataProvider edgeDatesProvider
     */
    public function testEdgeCasesForNextWeek($vanityWord, $currentWeek, $expectedWeek)
    {
        $this->configureDateForTestsAtRuntime($currentWeek);

        $this->loadFixtures(["NetworksAndServicesFixture"]);
        $client = static::createClient();

        $client->request('GET', '/schedules/p00rfdrb/' . $vanityWord);

        $this->assertRedirectTo($client, 302, '/schedules/p00rfdrb/' . $expectedWeek);
        $this->assertHasRequiredResponseHeaders($client);
    }

    public function edgeDatesProvider(): array
    {
        return [
            // [ action, current week, expected next week ]
            ['next_week', '2009/w53', '2010/w01'],
            ['next_week', '2011/w52', '2012/w01'],
            ['next_week', '2012/w52', '2013/w01'],
        ];
    }

    private function configureDateForTestsAtRuntime($currentWeek)
    {
        $piecesDateUrl = explode("/", $currentWeek);
        $year = $piecesDateUrl[0];
        $weekNumberOfYear = str_replace('w', '', $piecesDateUrl[1]);
        $chronos = new Chronos();
        $date = $chronos->setISODate((int) $year, (int) $weekNumberOfYear, 1); //year , week num of year , day of week

        ApplicationTime::setTime($date->getTimestamp());
    }

    protected function tearDown()
    {
        ApplicationTime::blank();
    }
}
