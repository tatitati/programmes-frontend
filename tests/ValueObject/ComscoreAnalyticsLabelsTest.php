<?php
declare(strict_types = 1);

namespace Tests\App\ValueObject;

use App\ValueObject\ComscoreAnalyticsLabels;
use App\ValueObject\CosmosInfo;
use App\ValueObject\IstatsAnalyticsLabels;
use PHPUnit\Framework\TestCase;
use Tests\App\DataFixtures\PagesService\BrandsFixtures;
use Tests\App\DataFixtures\PagesService\EpisodesFixtures;

class ComscoreAnalyticsLabelsTest extends TestCase
{
    public function testProgramme()
    {
        $programme = BrandsFixtures::hardTalk();
        $istatsLabels = new IstatsAnalyticsLabels($programme, 'programmes_container', '3', null);
        $cosmosInfo = new CosmosInfo('3', 'live');
        $comscoreLabels = new ComscoreAnalyticsLabels($programme, $cosmosInfo, $istatsLabels, 'https://www.bbc.co.uk/programmes/p004t1s0');

        $expectedLabels = [
            'c2' => '19999701',
            'ns_site' => 'bbc',
            'b_vs_un' => 'ws',
            'b_vs_ls' => 'english',
            'b_imp_src' => 'ws',
            'b_imp_ver' => '1.0.0.0',
            'b_app_type' => 'web',
            'b_app_name' => 'programmes|3',
            'b_page_type' => 'prog',
            'b_site_channel' => 'programmes',
            'b_site_section' => 'bbc_world_service',
            'b_prog_type' => 'programmes_container',
            'b_prog_brand_id' => 'p004t1s0',
            'b_prog_brand' => 'HARDtalk',
            'b_prog_title' => 'HARDtalk',
            'c1' => 2,
            'bbc_site' => 'iplayerradio',
            'c7' => 'https://www.bbc.co.uk/programmes/p004t1s0',
            'c8' => 'HARDtalk',
            'ns_c' => 'utf-8',
            'name' => 'programmes.p004t1s0',
        ];
        $this->assertEquals($expectedLabels, $comscoreLabels->getComscore()->getLabels());
    }

    public function testNonInternational()
    {
        $programme = BrandsFixtures::eastEnders();
        $istatsLabels = new IstatsAnalyticsLabels($programme, 'programmes_container', '3', null);
        $cosmosInfo = new CosmosInfo('3', 'live');
        $comscoreLabels = new ComscoreAnalyticsLabels($programme, $cosmosInfo, $istatsLabels, 'https://www.bbc.co.uk/programmes/b006m86d');
        $this->assertEquals(null, $comscoreLabels->getComscore());
    }
}
