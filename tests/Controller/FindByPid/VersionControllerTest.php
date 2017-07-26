<?php
declare(strict_types = 1);
namespace Tests\App\Controller\FindByPid;

use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use Cake\Chronos\Chronos;
use Tests\App\BaseWebTestCase;

/**
 * @covers App\Controller\FindByPid\VersionController
 */
class VersionControllerTest extends BaseWebTestCase
{
    public function testVersion()
    {
        $this->loadFixtures(["VersionsFixture"]);

        $client = static::createClient();
        $crawler = $client->request('GET', '/programmes/p4000001');

        $this->assertRedirectTo($client, 303, '/programmes/p3000001');
        $this->assertHasRequiredResponseHeaders($client);
    }
}
