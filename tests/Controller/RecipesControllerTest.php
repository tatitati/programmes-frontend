<?php
declare(strict_types=1);

namespace Tests\App\Controller;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Tests\App\BaseWebTestCase;

/**
 * @group recipes
 */
class RecipesControllerTest extends BaseWebTestCase
{
    public function setUp()
    {
        $this->loadFixtures([
            'ProgrammeEpisodes\EpisodesFixtures',
        ]);
    }

    /**
     * FULL PAGE
     */
    public function testFullPageDontBreak()
    {
        $client = $this->stubRecipeServerResponse();

        $client->request('GET', 'programmes/b013pqnm/recipes');

        $this->assertResponseStatusCode($client, 200);
    }

    public function test404ResponseWhenProgrammeIsNotFound()
    {
        $client = $this->stubRecipeServerResponse();

        $client->request('GET', 'programmes/b013zzz4/recipes');

        $this->assertResponseStatusCode($client, 404, 'When the PID programme doesnt exist non request is done to recipes server and the responde code is based on this fact');
    }

    /**
     * PARTIAL: using AMEN template
     */
    public function testValidResponseFromFoodApi()
    {
        $client = $this->stubRecipeServerResponse();

        $crawler = $client->request('GET', 'programmes/b013pqnm/recipes.ameninc');
        $this->assertCount(4, $crawler->filter('li'));

        $this->assertEquals('Stollen', trim($crawler->filter('[data-linktrack="recipe_1_title"]')->text()));
        $this->assertEquals('Traditional Christmas pudding with brandy butter', trim($crawler->filter('[data-linktrack="recipe_2_title"]')->text()));
        $this->assertEquals('Gluten-free gingerbread biscuits', trim($crawler->filter('[data-linktrack="recipe_3_title"]')->text()));
        $this->assertEquals('Apple and cinnamon kugelhopf with honeyed apples', trim($crawler->filter('[data-linktrack="recipe_4_title"]')->text()));
    }

    /**
     * PARTIAL: using Ds2013 template
     */
    public function testControllerAlsoWorkForD2013Design()
    {
        $client = $this->stubRecipeServerResponse();

        $crawler = $client->request('GET', 'programmes/b013pqnm/recipes.2013inc');

        $this->assertResponseStatusCode($client, 200);
        $this->assertTitlesRecipes([
            'Stollen',
            'Traditional Christmas pudding with brandy butter',
            'Gluten-free gingerbread biscuits',
            'Apple and cinnamon kugelhopf with honeyed apples',
        ], $crawler->filter('#recipes li h3 a'));
    }

    /**
     * edge responses from partial controller
     *
     * @dataProvider edgeResponseProvider
     */
    public function testEdgeResponses(Response $stubbedResponse)
    {
        $client = $this->createClientWithMockedGuzzleResponse($stubbedResponse);

        $client->request('GET', 'programmes/b013pqnm/recipes.ameninc');

        $this->assertResponseStatusCode($client, 204);
    }

    public function edgeResponseProvider()
    {
        $jsonWithNoResults = file_get_contents(__DIR__ . '/../DataFixtures/JSON/drwho.json');

        return [
            [new Response(200, [], '')],
            [new Response(404, [], null)],
            [new Response(200, [], $jsonWithNoResults)],
        ];
    }

    /**
     * helpers
     */
    private function createClientWithMockedGuzzleResponse(Response $response): Client
    {
        $stack = MockHandler::createWithMiddleware([$response]);
        $client = new GuzzleClient(['handler' => $stack]);

        $c = static::createClient();
        $c->getContainer()->set('csa_guzzle.client.default', $client);

        return $c;
    }

    private function assertTitlesRecipes(array $expectedTitles, Crawler $recipeTitles)
    {
        $extractedTitles = [];
        foreach ($recipeTitles as $title) {
            $extractedTitles[] = trim($title->textContent);
        }

        $this->assertEquals($expectedTitles, $extractedTitles);
    }

    private function stubRecipeServerResponse(): Client
    {
        $json = file_get_contents(__DIR__ . '/../DataFixtures/JSON/bakeoff.json');
        $response = new Response(200, [], $json);
        $client = $this->createClientWithMockedGuzzleResponse($response);

        return $client;
    }
}
