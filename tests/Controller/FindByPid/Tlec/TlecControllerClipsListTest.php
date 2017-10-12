<?php
declare(strict_types=1);

namespace Tests\App\Controller\FindByPid\Tlec;

use Symfony\Bundle\FrameworkBundle\Client;
use Tests\App\BaseWebTestCase;

class TlecControllerClipsListTest extends BaseWebTestCase
{
    /** @var Client */
    private $client;

    public function setUp()
    {
        $this->loadFixtures(["FindByPid\Tlec\TlecClipsFixture"]);
        $this->client = static::createClient();
    }

    public function testBrandWithNoClipsDoesntShowGrid()
    {
        $crawler = $this->client->request('GET', '/programmes/prstdbrnd1');
        $grid = $crawler->filter(".grid");
        $this->assertEquals(2, $grid->count());
    }

    /**
     * @dataProvider brandsClipsListProvider
     */
    public function testBrandClipsListTests(string $pid, int $childrenCount, int $trailingLinkCount)
    {
        $crawler = $this->client->request('GET', '/programmes/' . $pid);

        $grid = $crawler->filter(".grid");
        $this->assertEquals($childrenCount, $grid->children()->count());

        $trailingLink = $grid->children()->last()->filter('.media__footer');
        $this->assertEquals($trailingLinkCount, $trailingLink->count());
    }

    public function brandsClipsListProvider(): array
    {
        return [
            "Brand with less than four clips doesn't show trailing link" => ['prstdbrnd2', 2, 0],
            "Brand with exactly four clips doesn't show trailing link" => ['prstdbrnd3', 4, 0],
            "Brand with more than four clips shows trailing link" => ['prstdbrnd4', 4, 1],
        ];
    }
}
