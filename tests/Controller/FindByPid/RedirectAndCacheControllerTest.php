<?php
namespace Tests\App\Controller\FindByPid;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Client;
use Tests\App\BaseWebTestCase;

class RedirectAndCacheControllerTest extends BaseWebTestCase
{
    /** @var Client */
    protected $client;

    public function setUp()
    {
        $this->loadFixtures([
            'ProgrammeEpisodes\EpisodesFixtures',
        ]);
        $this->client = static::createClient();
    }

    public function testRedirectToFragmentBroadcastWithCaching()
    {
        $this->userVisitEpisode("programmes/p3000002/broadcasts");

        $this->thenCacheRedirectTo('/programmes/p3000002#broadcasts');
    }

    public function testRedirectToCreditsFragment()
    {
        $this->userVisitEpisode("programmes/p3000002/credits");

        $this->thenCacheRedirectTo('/programmes/p3000002#credits');
    }

    private function thenCacheRedirectTo($redirectTo)
    {
        $this->assertResponseStatusCode($this->client, 301, 'Redirect should be permanent(301)');
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse, 'Response should be a RedirectResponse');

        /** @var RedirectResponse $response */
        $response = $this->client->getResponse();
        $this->assertContains($redirectTo, $response->getTargetUrl());
        $this->assertEquals(
            "max-age=3600, public",
            $this->client->getResponse()->headers->get('cache-control'),
            'It should have caching headers in the response'
        );
    }

    private function userVisitEpisode(string $url)
    {
        $this->client->request('GET', $url);
    }
}
