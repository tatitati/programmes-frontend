<?php
declare(strict_types=1);

namespace Tests\App\Controller\Partial;

use GuzzleHttp\Client as GuzzleClient;
use Symfony\Bundle\FrameworkBundle\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Tests\App\BaseWebTestCase;

class RecipesControllerTest extends BaseWebTestCase
{
    public function testValidResponseFromFoodApi()
    {
        $json = file_get_contents(__DIR__ . '/JSON/bakeoff.json');
        $response = new Response(200, [], $json);
        $client = $this->createClientWithMockedGuzzleResponse($response);

        $crawler = $client->request('GET', 'programmes/b013pqnm/recipes.ameninc');
        $this->assertCount(4, $crawler->filter('li'));

        $this->assertEquals('Stollen', trim($crawler->filter('[data-linktrack="programmes_recipe_1_title"]')->text()));
        $this->assertEquals('Traditional Christmas pudding with brandy butter', trim($crawler->filter('[data-linktrack="programmes_recipe_2_title"]')->text()));
        $this->assertEquals('Gluten-free gingerbread biscuits', trim($crawler->filter('[data-linktrack="programmes_recipe_3_title"]')->text()));
        $this->assertEquals('Apple and cinnamon kugelhopf with honeyed apples', trim($crawler->filter('[data-linktrack="programmes_recipe_4_title"]')->text()));
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
        $json = file_get_contents(__DIR__ . '/JSON/drwho.json');
        $response = new Response(200, [], $json);
        $client = $this->createClientWithMockedGuzzleResponse($response);

        $client->request('GET', 'programmes/b006q2x0/recipes.ameninc');
        $this->assertResponseStatusCode($client, 404);
    }

    private function createClientWithMockedGuzzleResponse(Response $response): Client
    {
        $mockHandler = new MockHandler();
        $container = [];
        $stack = HandlerStack::create($mockHandler);
        $history = Middleware::history($container);
        $stack->push($history);

        $client = new GuzzleClient(['handler' => $stack]);
        $mockHandler->append($response);

        $c = static::createClient();
        $c->getContainer()->set('csa_guzzle.client.default', $client);

        return $c;
    }
}
