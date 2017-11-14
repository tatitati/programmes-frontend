<?php
declare(strict_types = 1);

namespace Tests\App\Controller\Styleguide\Amen;

use GuzzleHttp\Client;
use Tests\App\BaseWebTestCase;

class PromotionControllerTest extends BaseWebTestCase
{
    /** @var Client */
    private $httpClient;

    public function setUp()
    {
        $this->httpClient = static::createClient();
    }

    public function testPromotionAmenRouteIsFound()
    {
        $this->httpClient->request('GET', '/programmes/styleguide/amen/promotion');

        $this->assertEquals(200, $this->httpClient->getResponse()->getStatusCode());
    }
}
