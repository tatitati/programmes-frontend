<?php
declare(strict_types = 1);
namespace Tests\App\Controller;

use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Options;
use BBC\ProgrammesPagesService\Service\CoreEntitiesService;
use BBC\ProgrammesPagesService\Service\ServiceFactory;
use Tests\App\BaseWebTestCase;

/**
 * This test asserts that Programmes with redirects configured in their options
 * trigger those redirects, and that those redirects are cached.
 */
class PidOverrideHandlerTest extends BaseWebTestCase
{
    /**
     * @dataProvider redirectsDataProvider
     */
    public function testRedirectsFromFindByPidPage(string $url, int $redirectStatusCode, string $redirectLocation)
    {
        $coreEntity = $this->createConfiguredMock(CoreEntity::class, [
            'getOptions' => new Options([
                'pid_override_code' => $redirectStatusCode,
                'pid_override_url' => $redirectLocation,
            ]),
        ]);

        $service = $this->createConfiguredMock(CoreEntitiesService::class, [
            'findByPidFull' => $coreEntity,
        ]);

        $serviceFactory = $this->createConfiguredMock(ServiceFactory::class, [
            'getCoreEntitiesService' => $service,
        ]);

        $client = static::createClient();
        $client->getContainer()->set(ServiceFactory::class, $serviceFactory);

        $crawler = $client->request('GET', $url);

        $this->assertRedirectTo($client, $redirectStatusCode, $redirectLocation);
        $this->assertEquals('max-age=3600, public', $client->getResponse()->headers->get('Cache-Control'));
    }

    public function redirectsDataProvider()
    {
        return [
            'redirect from find-by-pid page' => [
                '/programmes/b0000001', 301, 'http://example.com/test',
            ],
            'redirect from aggregate page' => [
                '/programmes/b0000001/episodes/player', 302, 'http://example.org/wibble',
            ],
        ];
    }
}
