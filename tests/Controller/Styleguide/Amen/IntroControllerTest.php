<?php
declare(strict_types = 1);

namespace Tests\App\Controller\Styleguide\Amen;

use GuzzleHttp\Client;
use Tests\App\BaseWebTestCase;

class IntroControllerTest extends BaseWebTestCase
{
    /** @var Client */
    private $httpClient;

    public function setUp()
    {
        $this->httpClient = static::createClient();
    }

    public function testAmenIntroRouteIsFound()
    {
        $this->httpClient->request('GET', '/programmes/styleguide/amen');

        $this->assertEquals(200, $this->httpClient->getResponse()->getStatusCode());
    }
}
