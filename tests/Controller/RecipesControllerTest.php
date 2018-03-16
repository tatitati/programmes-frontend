<?php
declare(strict_types=1);

namespace Tests\App\Controller;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Symfony\Bundle\FrameworkBundle\Client;
use Tests\App\BaseWebTestCase;

class RecipesControllerTest extends BaseWebTestCase
{
    public function testValidResponseFromFoodApi()
    {
        $json = file_get_contents(__DIR__ . '/../DataFixtures/JSON/bakeoff.json');
        $response = new Response(200, [], $json);
        $client = $this->createClientWithMockedGuzzleResponse($response);

        $crawler = $client->request('GET', 'programmes/b013pqnm/recipes.ameninc');
        $this->assertCount(4, $crawler->filter('li'));

        $this->assertEquals('Stollen', trim($crawler->filter('[data-linktrack="recipe_1_title"]')->text()));
        $this->assertEquals('Traditional Christmas pudding with brandy butter', trim($crawler->filter('[data-linktrack="recipe_2_title"]')->text()));
        $this->assertEquals('Gluten-free gingerbread biscuits', trim($crawler->filter('[data-linktrack="recipe_3_title"]')->text()));
        $this->assertEquals('Apple and cinnamon kugelhopf with honeyed apples', trim($crawler->filter('[data-linktrack="recipe_4_title"]')->text()));
    }

    public function testEmptyResponseFromFoodApi()
    {
        $client = $this->createClientWithMockedGuzzleResponse(new Response(200, [], ''));
        $client->request('GET', 'programmes/b013pqnm/recipes.ameninc');
        $this->assertResponseStatusCode($client, 404);
    }

    public function testProgrammeIsNotFoundInFoodApi()
    {
        $client = $this->createClientWithMockedGuzzleResponse(new Response(404, [], null));
        $client->request('GET', 'programmes/b013pqnm/recipes.ameninc');
        $this->assertResponseStatusCode($client, 404);
    }

    public function testProgrammeHasNoRecipesInFoodApi()
    {
        $json = file_get_contents(__DIR__ . '/../DataFixtures/JSON/drwho.json');
        $response = new Response(200, [], $json);
        $client = $this->createClientWithMockedGuzzleResponse($response);

        $client->request('GET', 'programmes/b006q2x0/recipes.ameninc');
        $this->assertResponseStatusCode($client, 404);
    }

    private function createClientWithMockedGuzzleResponse(Response $response): Client
    {
        $stack = MockHandler::createWithMiddleware([$response]);
        $client = new GuzzleClient(['handler' => $stack]);

        $c = static::createClient();
        $c->getContainer()->set('csa_guzzle.client.default', $client);

        return $c;
    }
}
