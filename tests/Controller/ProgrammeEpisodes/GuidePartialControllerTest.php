<?php
declare(strict_types = 1);
namespace Tests\App\Controller\ProgrammeEpisodes;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Tests\App\BaseWebTestCase;

class GuidePartialControllerTest extends BaseWebTestCase
{
    /** @var Client */
    protected $client;

    /** @var Crawler */
    private $crawler;

    public function setUp()
    {
        $this->loadFixtures([
            'ProgrammeEpisodes\EpisodesFixtures',
        ]);

        $this->client = static::createClient();
    }

    /**
     * @dataProvider nestedLevelsProvider
     */
    public function testNestedLevelOnlyTweakHeadersSize(string $providedNestedLevel, string $expectedHeaderSize)
    {
        $this->userVisitPartial('/programmes/b006q2x0/episodes/guide.2013inc' . $providedNestedLevel);

        $this->thenUserSeeTheseTitles([
            'B1-S2',
            'B1-S1',
            'B1-E1',
        ]);
        $this->thenTheseTitlesHaveThisSize($expectedHeaderSize);
    }

    public function nestedLevelsProvider()
    {
        return [
            ['', 'h1'],
            ['?nestedlevel=1', 'h1'],
            ['?nestedlevel=3', 'h3'],
            ['?nestedlevel=8', 'h8'],
        ];
    }

    /**
     * Helpers
     */
    private function userVisitPartial(string $url)
    {
        $this->crawler = $this->client->request('GET', $url);

        $this->assertResponseStatusCode($this->client, 200);
    }

    private function thenTheseTitlesHaveThisSize($expectedSizeHeader)
    {
        $this->assertSame(3, $this->crawler->filter($expectedSizeHeader)->count());
    }

    private function thenUserSeeTheseTitles(array $expectedListOfTitles)
    {
        $seriesTitles = $this->crawler->filter('.episode-guide__series-container .series__title');

        $titlesText = [];
        foreach ($seriesTitles as $seriesTitle) {
            $titlesText[] = $seriesTitle->textContent;
        }

        $titlesEpisodes = $this->crawler->filter('.programme--episode .programme__title');
        foreach ($titlesEpisodes as $titleEpisode) {
            $titlesText[] = $titleEpisode->textContent;
        }



        $this->assertEquals($expectedListOfTitles, $titlesText);
    }
}
