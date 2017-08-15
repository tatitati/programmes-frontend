<?php
declare(strict_types = 1);
namespace Tests\App\ValueObject;

use App\ValueObject\AnalyticsCounterName;
use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\Entity\Unfetched\UnfetchedProgrammeItem;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Sid;
use PHPUnit\Framework\TestCase;

/**
 * Based on V2 BBC_Programmes_Helper_Nedstat_Test
 */
class CounterNameTest extends TestCase
{
    public function testHomePage()
    {
        $url = '/programmes';
        $counterName = new AnalyticsCounterName(null, $url);
        $this->assertEquals('programmes.home.page', $counterName);
    }

    public function testDodgyCharacters()
    {
        $url = '/programmes/a!b@c$d-e%f^g&h*i(j)k';
        $counterName = new AnalyticsCounterName(null, $url);
        $this->assertEquals('programmes.a_b_c_d_e_f_g_h_i_j_k.page', $counterName);

        // note: multiyte characters get multi escaped by preg_replace
        $url = '/programmes/a££b';
        $counterName = new AnalyticsCounterName(null, $url);
        $this->assertEquals('programmes.a_b.page', $counterName);
    }

    public function testAllLowerCase()
    {
        $brand = $this->brandFactory('b00bbbbb', 'LoTs Of CaMel CaSe');

        $counterName = new AnalyticsCounterName($brand, '/programmes/b00bbbbb');
        $this->assertEquals('programmes.lots_of_camel_case.brand.b00bbbbb.page', $counterName);

        $counterName = new AnalyticsCounterName(null, '/programmes/SHOUTING');
        $this->assertEquals('programmes.shouting.page', $counterName);
    }

    public function testMultipleUnderscores()
    {
        $brand = $this->brandFactory('b00bbbbb', 'A Brand   With %= more underscores');
        $counterName = new AnalyticsCounterName($brand, '/programmes/b00bbbbb');
        $this->assertEquals('programmes.a_brand_with_more_underscores.brand.b00bbbbb.page', $counterName);
    }

    public function testUnderscoreTrim()
    {
        $brand = $this->brandFactory('b00bbbbb', '(brackets) Create underscores (they do)');
        $counterName = new AnalyticsCounterName($brand, '/programmes/b00bbbbb');
        $this->assertEquals('programmes.brackets_create_underscores_they_do.brand.b00bbbbb.page', $counterName);
    }

    public function testAz()
    {
        $counterName = new AnalyticsCounterName(null, '/programmes/a-z');
        $this->assertEquals('programmes.a_z.page', $counterName);
    }

    public function testGenres()
    {
        $counterName = new AnalyticsCounterName(null, '/programmes/genres');
        $this->assertEquals('programmes.genres.page', $counterName);
    }

    public function testProgrammeBrand()
    {
        $brand = $this->brandFactory('b00bbbbb', 'This is a brand');
        $counterName = new AnalyticsCounterName($brand, '/programmes/b00bbbbb');
        $this->assertEquals('programmes.this_is_a_brand.brand.b00bbbbb.page', $counterName);
    }

    public function testProgrammeSubBrand()
    {
        $brand = $this->brandFactory('b00bbbbb', 'This is a brand');

        $counterName = new AnalyticsCounterName($brand, '/programmes/b00bbbbb/episodes/player');
        $this->assertEquals('programmes.this_is_a_brand.brand.b00bbbbb.episodes.player.page', $counterName);

        $counterName = new AnalyticsCounterName($brand, '/programmes/b00bbbbb/clips');
        $this->assertEquals('programmes.this_is_a_brand.brand.b00bbbbb.clips.page', $counterName);

        $counterName = new AnalyticsCounterName($brand, '/programmes/b00bbbbb/galleries');
        $this->assertEquals('programmes.this_is_a_brand.brand.b00bbbbb.galleries.page', $counterName);

        $counterName = new AnalyticsCounterName($brand, '/programmes/b00bbbbb/broadcasts/2017/07');
        $this->assertEquals('programmes.this_is_a_brand.brand.b00bbbbb.broadcasts.2017.07.page', $counterName);
    }

    public function testProgrammeSeries()
    {
        $brand = $this->brandFactory('b00ssssss', 'This is a brand');
        $series = $this->seriesFactory('b00ssssss', 'This is a series', $brand);
        $counterName = new AnalyticsCounterName($series, '/programmes/b00ssssss');
        $this->assertEquals('programmes.this_is_a_brand.this_is_a_series.series.b00ssssss.page', $counterName);
    }

    public function testProgrammeEpisode()
    {
        $brand = $this->brandFactory('b00bbbbb', 'This is a brand');
        $series = $this->seriesFactory('b00ssssss', 'This is a series', $brand);
        $episode = $this->episodeFactory('b00ppppp', 'This is an episode', $series);
        $counterName = new AnalyticsCounterName($episode, '/programmes/b00ppppp');
        $this->assertEquals('programmes.this_is_a_brand.this_is_a_series.this_is_an_episode.episode.b00ppppp.page', $counterName);
    }

    public function testProgrammeEpisodeSubPage()
    {
        $brand = $this->brandFactory('b00bbbbb', 'This is a brand');
        $series = $this->seriesFactory('b00ssssss', 'This is a series', $brand);
        $episode = $this->episodeFactory('b00ppppp', 'This is an episode', $series);
        $counterName = new AnalyticsCounterName($episode, '/programmes/b00ppppp/clips');
        $this->assertEquals('programmes.this_is_a_brand.this_is_a_series.this_is_an_episode.episode.b00ppppp.clips.page', $counterName);
    }

    public function testProgrammeClip()
    {
        $brand = $this->brandFactory('b00bbbbb', 'This is a brand');
        $series = $this->seriesFactory('b00ssssss', 'This is a series', $brand);
        $episode = $this->episodeFactory('b00ppppp', 'This is an episode', $series);
        $clip = $this->clipFactory('b00ccccc', 'This is a clip', $episode);
        $counterName = new AnalyticsCounterName($clip, '/programmes/b00ccccc');
        $this->assertEquals('programmes.this_is_a_brand.this_is_a_series.this_is_an_episode.this_is_a_clip.clip.b00ccccc.page', $counterName);
    }

    public function testSchedules()
    {
        $sid = new Sid('bbc_two_england');
        $pid = new Pid('p00fzl97');
        $service = new Service(
            0,
            $sid,
            $pid,
            'Name'
        );

        $counterName = new AnalyticsCounterName(null, '/schedules');
        $this->assertEquals('programmes.home.schedules.page', $counterName);

        $counterName = new AnalyticsCounterName($service, '/schedules/p00fzl97');
        $this->assertEquals('programmes.schedules.bbc_two_england.page', $counterName);

        $counterName = new AnalyticsCounterName($service, '/schedules/p00fzl97/2017');
        $this->assertEquals('programmes.schedules.bbc_two_england.2017.page', $counterName);

        $counterName = new AnalyticsCounterName($service, '/schedules/p00fzl97/2017-07');
        $this->assertEquals('programmes.schedules.bbc_two_england.2017.07.page', $counterName);

        $counterName = new AnalyticsCounterName($service, '/schedules/p00fzl97/2017-07-06');
        $this->assertEquals('programmes.schedules.bbc_two_england.2017.07.06.page', $counterName);
    }

    private function brandFactory($pid, $title)
    {
        $brand = $this->createMock(Brand::class);
        $brand->method('getPid')->willReturn(new Pid($pid));
        $brand->method('getTitle')->willReturn(($title));
        $brand->method('getType')->willReturn('brand');
        return $brand;
    }

    private function seriesFactory($pid, $title, $parent = null)
    {
        $series = $this->createMock(Series::class);
        $series->method('getPid')->willReturn(new Pid($pid));
        $series->method('getTitle')->willReturn($title);
        $series->method('getParent')->willReturn($parent);
        $series->method('getType')->willReturn('series');
        return $series;
    }

    private function episodeFactory($pid, $title, $parent = null)
    {
        $episode = $this->createMock(Episode::class);
        $episode->method('getPid')->willReturn(new Pid($pid));
        $episode->method('getTitle')->willReturn($title);
        $episode->method('getParent')->willReturn($parent);
        $episode->method('getType')->willReturn('episode');
        return $episode;
    }

    private function clipFactory($pid, $title, $parent = null)
    {
        $clip = $this->createMock(Clip::class);
        $clip->method('getPid')->willReturn(new Pid($pid));
        $clip->method('getTitle')->willReturn(($title));
        $clip->method('getParent')->willReturn(($parent));
        $clip->method('getType')->willReturn('clip');
        return $clip;
    }
}
