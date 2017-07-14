<?php
declare(strict_types = 1);
namespace Tests\App;

use Liip\FunctionalTestBundle\Test\WebTestCase;

abstract class BaseWebTestCase extends WebTestCase
{
    const FIXTURES_PATH = 'Tests\App\DataFixtures\ORM\\';

    public function assertResponseStatusCode($client, $expectedCode)
    {
        $actualCode = $client->getResponse()->getStatusCode();
        $this->assertEquals($expectedCode, $actualCode, sprintf(
            'Failed asserting that the response status code "%s" matches expected "%s"',
            $actualCode,
            $expectedCode
        ));
    }

    public function assertRedirectTo($client, $code, $expectedLocation)
    {
        $this->assertResponseStatusCode($client, $code);
        $this->assertEquals($expectedLocation, $client->getResponse()->headers->get('location'));
    }

    public function assertHasRequiredResponseHeaders($client, $cacheControl = 'max-age=120, public', $contentLanguage = null)
    {
        $this->assertEquals($cacheControl, $client->getResponse()->headers->get('Cache-Control'));
        $this->assertArraySubset(['X-CDN', 'X-BBC-Edge-Scheme'], $client->getResponse()->getVary());
        $this->assertEquals('IE=edge', $client->getResponse()->headers->get('X-UA-Compatible'));
        $this->assertEquals('programmes-frontend', $client->getResponse()->headers->get('X-Webapp'));
        $this->assertEquals('stale-while-revalidate=30', $client->getResponse()->headers->get('X-Cache-Control'));
        if (isset($contentLanguage)) {
            $this->assertEquals($contentLanguage, $client->getResponse()->headers->get('Content-Language'));
        } else {
            $this->assertNotEmpty($client->getResponse()->headers->get('Content-Language'));
        }
    }

    protected function loadFixtures(array $fixtureNames, $omName = null, $registryName = 'doctrine', $purgeMode = null)
    {
        $classNames = array();
        foreach ($fixtureNames as $fixtureName) {
            $className = self::FIXTURES_PATH . $fixtureName;
            array_push($classNames, $className);
        }
        parent::loadFixtures($classNames, $omName, $registryName, $purgeMode);
    }
}
