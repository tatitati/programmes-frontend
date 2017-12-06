<?php
declare(strict_types = 1);
namespace Tests\App\ValueObject;

use App\ValueObject\IstatsAnalyticsLabels;
use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Entity\Genre;
use BBC\ProgrammesPagesService\Domain\Entity\MasterBrand;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Options;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\ValueObject\Mid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Nid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use PHPUnit\Framework\TestCase;

class IstatsAnalyticsLabelsTest extends TestCase
{
    public function testService()
    {
        $context = $this->serviceFactory('bbc_one', 'tv');
        $labels = $this->getAnalyticsLabels(
            $context,
            'App\Controller\SchedulesByDayController',
            '123',
            ['extraLabel' => 'extraValue']
        );
        $expectedLabels = [
            'app_name' => 'programmes',
            'prod_name' => 'programmes',
            'progs_page_type' => 'App\Controller\SchedulesByDayController',
            'app_version' => '123',
            'bbc_site' => 'tvandiplayer',
            'event_master_brand' => 'bbc_one',
            'extraLabel' => 'extraValue',
            'accept_language' => '',
        ];
        $this->assertEquals($expectedLabels, $labels);
    }

    public function testProgramme()
    {
        $context = $this->brandFactory('b006q2x0', 'Doctor Who', 'bbc_one', 'bbc_one', 'tv', 'C00035');
        $labels = $this->getAnalyticsLabels($context, 'App\Controller\FindByPid\TlecController', '123');
        $expectedLabels = [
            'app_name' => 'programmes',
            'prod_name' => 'programmes',
            'rec_app_id' => 'programmes',
            'progs_page_type' => 'App\Controller\FindByPid\TlecController',
            'app_version' => '123',
            'rec_v' => '2',
            'bbc_site' => 'tvandiplayer',
            'event_master_brand' => 'bbc_one',
            'programme_title' => 'Doctor Who',
            'brand_title' => 'Doctor Who',
            'pips_genre_group_ids' => 'C00035',
            'brand_id' => 'b006q2x0',
            'rec_p' => 'null_null_2',
            'container_is' => 'brand',
            'is_tleo' => 'true',
            'accept_language' => '',
        ];
        $this->assertEquals($expectedLabels, $labels);
    }

    public function testIstatSetProperBbcSiteTagMatchinOptionValue()
    {
        $context = $this->brandFactory('b01mwvng', 'Doctor Who', 'bbc_one', 'bbc_one', 'tv', 'C00035', ['bbc_site' => 'arts']);

        $labels = new IstatsAnalyticsLabels($context, 'App\Controller\FindByPid\TlecController', '123', []);
        $expectedLabels = [
            'app_name' => 'programmes',
            'prod_name' => 'programmes',
            'app_version' => '123',
            'accept_language' => '',
            'progs_page_type' => 'App\Controller\FindByPid\TlecController',
            'programme_title' => 'Doctor Who',
            'brand_title' => 'Doctor Who',
            'pips_genre_group_ids' => 'C00035',
            'rec_v' => '2',
            'rec_app_id' => 'programmes',
            'rec_p' => 'null_null_2',
            // we found options regarding to bbc_site, so we set them
            'container_is' => 'brand',
            'is_tleo' => 'true',
            'bbc_site' => 'arts',
            'event_master_brand' => 'bbc_one',
            'brand_id' => 'b01mwvng',
        ];
        $this->assertEquals($expectedLabels, $labels->getLabels());
    }

    public function testIstatSetProperBbcSiteTagMatchinNetworkMediumWhenThereIsNoValueStored()
    {
        $context = $this->brandFactory('b01mwvng', 'Doctor Who', 'bbc_one', 'bbc_one', 'tv', 'C00035', []);

        $labels = new IstatsAnalyticsLabels($context, 'App\Controller\FindByPid\TlecController', '123', []);
        $expectedLabels = [
            'app_name' => 'programmes',
            'prod_name' => 'programmes',
            'app_version' => '123',
            'accept_language' => '',
            'progs_page_type' => 'App\Controller\FindByPid\TlecController',
            'programme_title' => 'Doctor Who',
            'brand_title' => 'Doctor Who',
            'pips_genre_group_ids' => 'C00035',
            'rec_v' => '2',
            'rec_app_id' => 'programmes',
            'rec_p' => 'null_null_2',
            'container_is' => 'brand',
            'is_tleo' => 'true',
            // not found options regarding to bbc_site, so we set value
            // checking network/masterbrand
            'bbc_site' => 'tvandiplayer',
            'event_master_brand' => 'bbc_one',
            'brand_id' => 'b01mwvng',
        ];
        $this->assertEquals($expectedLabels, $labels->getLabels());
    }

    private function serviceFactory(string $networkId, string $networkMedium)
    {
        $service = $this->createMock(Service::class);
        if (!empty($networkId) && !empty($networkMedium)) {
            $service->method('getNetwork')->willReturn($this->networkFactory($networkId, $networkMedium));
        } else {
            $service->method('getNetwork')->willReturn(null);
        }
        return $service;
    }

    private function brandFactory($pid, $title, $mid, $networkId, $networkMedium, $genreId, $options = [])
    {
        $genre = $this->createMock(Genre::class);
        $genre->method('getId')->willReturn($genreId);

        $masterBrand = $this->createMock(MasterBrand::class);
        $masterBrand->method('getMid')->willReturn(new Mid($mid));

        $brand = $this->createMock(Brand::class);
        $brand->method('getPid')->willReturn(new Pid($pid));
        $brand->method('getTitle')->willReturn(($title));
        $brand->method('getTleo')->willReturn($brand);
        $brand->method('getAncestry')->willReturn([$brand]);
        $brand->method('getGenres')->willReturn([$genre]);
        $brand->method('getMasterBrand')->willReturn($masterBrand);
        $brand->method('getPid')->willReturn(new Pid($pid));
        $brand->method('getNetwork')->willReturn($this->networkFactory($networkId, $networkMedium));
        $brand->method('getType')->willReturn('brand');
        $brand->method('isTleo')->willReturn(true);
        $brand->method('getOptions')->willReturn(new Options($options));
        $brand->method('getOption')->will(
            $this->returnCallback(function ($o) use ($brand) {
                return $brand->getOptions()->getOption($o);
            })
        );

        return $brand;
    }

    private function networkFactory(string $nid, string $medium = '')
    {
        $network = $this->createMock(Network::class);
        $network->method('getNid')->willReturn(new Nid($nid));
        if ($medium === 'tv') {
            $network->method('isTv')->willReturn(true);
        } elseif ($medium === 'radio') {
            $network->method('isRadio')->willReturn(true);
        }
        return $network;
    }

    private function getAnalyticsLabels($context, string $controllerName, string $appVersion, array $extraLabels = [])
    {
        $labelsArray = [];
        $analyticsLabels = new IstatsAnalyticsLabels($context, $controllerName, $appVersion, $extraLabels);
        $labels = $analyticsLabels->orbLabels();
        foreach ($labels as $label) {
            $labelsArray[$label['key']] =  urldecode($label['value']);
        }
        return $labelsArray;
    }
}
