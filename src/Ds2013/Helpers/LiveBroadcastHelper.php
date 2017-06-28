<?php
declare(strict_types = 1);
namespace App\Ds2013\Helpers;

use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Chronos;

class LiveBroadcastHelper
{
    private const LIVE_SERVICE_URLS = [
        'bbc_one_london'                        => '/iplayer/live/bbcone',
        'bbc_one_scotland'                      => '/iplayer/live/bbcone?area=scotland',
        'bbc_one_wales'                         => '/iplayer/live/bbcone?area=wales',
        'bbc_one_northern_ireland'              => '/iplayer/live/bbcone?area=northern_ireland',
        'bbc_one_cambridge'                     => '/iplayer/live/bbcone?area=cambridge',
        'bbc_one_channel_islands'               => '/iplayer/live/bbcone?area=channel_islands',
        'bbc_one_east'                          => '/iplayer/live/bbcone?area=east',
        'bbc_one_east_midlands'                 => '/iplayer/live/bbcone?area=east_midlands',
        'bbc_one_east_yorkshire'                => '/iplayer/live/bbcone?area=east_yorkshire',
        'bbc_one_north_east'                    => '/iplayer/live/bbcone?area=north_east',
        'bbc_one_north_west'                    => '/iplayer/live/bbcone?area=north_west',
        'bbc_one_oxford'                        => '/iplayer/live/bbcone?area=oxford',
        'bbc_one_south'                         => '/iplayer/live/bbcone?area=south',
        'bbc_one_south_east'                    => '/iplayer/live/bbcone?area=south_east',
        'bbc_one_south_west'                    => '/iplayer/live/bbcone?area=south_west',
        'bbc_one_west'                          => '/iplayer/live/bbcone?area=west',
        'bbc_one_west_midlands'                 => '/iplayer/live/bbcone?area=west_midlands',
        'bbc_one_yorks'                         => '/iplayer/live/bbcone?area=yorks',
        'bbc_two_england'                       => '/iplayer/live/bbctwo',
        // @TODO are these correct for bbc_two regions
        'bbc_two_northern_ireland_digital'      => '/iplayer/live/bbctwo?area=northern_ireland_digital',
        'bbc_two_wales_digital'                 => '/iplayer/live/bbctwo?area=wales_digital',
        'bbc_two_scotland'                      => '/iplayer/live/bbctwo?area=scotland',
        'bbc_three'                             => '/iplayer/live/bbcthree',
        'bbc_four'                              => '/iplayer/live/bbcfour',
        'cbbc'                                  => '/iplayer/live/cbbc',
        'cbeebies'                              => '/iplayer/live/cbeebies',
        'bbc_news24'                            => '/iplayer/live/bbcnews',
        'bbc_parliament'                        => '/iplayer/live/bbcparliament',
        'bbc_alba'                              => '/iplayer/live/bbcalba',
        'bbc_radio_one'                         => '/radio/player/bbc_radio_one',
        'bbc_1xtra'                             => '/radio/player/bbc_1xtra',
        'bbc_radio_two'                         => '/radio/player/bbc_radio_two',
        'bbc_radio_three'                       => '/radio/player/bbc_radio_three',
        'bbc_radio_fourfm'                      => '/radio/player/bbc_radio_fourfm',
        'bbc_radio_fourlw'                      => '/radio/player/bbc_radio_fourlw',
        'bbc_radio_four_extra'                  => '/radio/player/bbc_radio_four_extra',
        'bbc_radio_five_live'                   => '/radio/player/bbc_radio_five_live',
        'bbc_radio_five_live_sports_extra'      => '/radio/player/bbc_radio_five_live_sports_extra',
        'bbc_6music'                            => '/radio/player/bbc_6music',
        'bbc_7'                                 => '/radio/player/bbc_7',
        'bbc_asian_network'                     => '/radio/player/bbc_asian_network',
        'bbc_world_service'                     => '/radio/player/bbc_world_service',
        'bbc_radio_scotland_fm'                 => '/radio/player/bbc_radio_scotland_fm',
        'bbc_radio_nan_gaidheal'                => '/radio/player/bbc_radio_nan_gaidheal',
        'bbc_radio_ulster'                      => '/radio/player/bbc_radio_ulster',
        'bbc_radio_foyle'                       => '/radio/player/bbc_radio_foyle',
        'bbc_radio_wales_fm'                    => '/radio/player/bbc_radio_wales_fm',
        'bbc_radio_cymru'                       => '/radio/player/bbc_radio_cymru',
        'bbc_radio_cymru_mwy'                   => '/radio/player/bbc_radio_cymru_mwy',
        'bbc_london'                            => '/radio/player/bbc_london',
        'bbc_radio_berkshire'                   => '/radio/player/bbc_radio_berkshire',
        'bbc_radio_bristol'                     => '/radio/player/bbc_radio_bristol',
        'bbc_radio_cambridge'                   => '/radio/player/bbc_radio_cambridge',
        'bbc_radio_cornwall'                    => '/radio/player/bbc_radio_cornwall',
        'bbc_radio_coventry_warwickshire'       => '/radio/player/bbc_radio_coventry_warwickshire',
        'bbc_radio_cumbria'                     => '/radio/player/bbc_radio_cumbria',
        'bbc_radio_derby'                       => '/radio/player/bbc_radio_derby',
        'bbc_radio_devon'                       => '/radio/player/bbc_radio_devon',
        'bbc_radio_essex'                       => '/radio/player/bbc_radio_essex',
        'bbc_radio_gloucestershire'             => '/radio/player/bbc_radio_gloucestershire',
        'bbc_radio_guernsey'                    => '/radio/player/bbc_radio_guernsey',
        'bbc_radio_hereford_worcester'          => '/radio/player/bbc_radio_hereford_worcester',
        'bbc_radio_humberside'                  => '/radio/player/bbc_radio_humberside',
        'bbc_radio_jersey'                      => '/radio/player/bbc_radio_jersey',
        'bbc_radio_kent'                        => '/radio/player/bbc_radio_kent',
        'bbc_radio_lancashire'                  => '/radio/player/bbc_radio_lancashire',
        'bbc_radio_leeds'                       => '/radio/player/bbc_radio_leeds',
        'bbc_radio_leicester'                   => '/radio/player/bbc_radio_leicester',
        'bbc_radio_lincolnshire'                => '/radio/player/bbc_radio_lincolnshire',
        'bbc_radio_manchester'                  => '/radio/player/bbc_radio_manchester',
        'bbc_radio_merseyside'                  => '/radio/player/bbc_radio_merseyside',
        'bbc_radio_newcastle'                   => '/radio/player/bbc_radio_newcastle',
        'bbc_radio_norfolk'                     => '/radio/player/bbc_radio_norfolk',
        'bbc_radio_northampton'                 => '/radio/player/bbc_radio_northampton',
        'bbc_radio_nottingham'                  => '/radio/player/bbc_radio_nottingham',
        'bbc_radio_oxford'                      => '/radio/player/bbc_radio_oxford',
        'bbc_radio_sheffield'                   => '/radio/player/bbc_radio_sheffield',
        'bbc_radio_shropshire'                  => '/radio/player/bbc_radio_shropshire',
        'bbc_radio_solent'                      => '/radio/player/bbc_radio_solent',
        'bbc_radio_somerset_sound'              => '/radio/player/bbc_radio_somerset_sound',
        'bbc_radio_stoke'                       => '/radio/player/bbc_radio_stoke',
        'bbc_radio_suffolk'                     => '/radio/player/bbc_radio_suffolk',
        'bbc_radio_surrey'                      => '/radio/player/bbc_radio_surrey',
        'bbc_radio_sussex'                      => '/radio/player/bbc_radio_sussex',
        'bbc_radio_swindon'                     => '/radio/player/bbc_radio_swindon',
        'bbc_radio_wiltshire'                   => '/radio/player/bbc_radio_wiltshire',
        'bbc_radio_york'                        => '/radio/player/bbc_radio_york',
        'bbc_southern_counties_radio'           => '/radio/player/bbc_southern_counties_radio',
        'bbc_tees'                              => '/radio/player/bbc_tees',
        'bbc_three_counties_radio'              => '/radio/player/bbc_three_counties_radio',
        'bbc_wm'                                => '/radio/player/bbc_wm',
        'bbc_music_jazz'                        => '/radio/player/bbc_music_jazz',
        'bbc_radio_two_fifties'                 => '/radio/player/bbc_radio_two_fifties',
        'bbc_radio_scotland_music_extra'        => '/radio/player/bbc_radio_scotland_music_extra',
        'bbc_radio_two_country'                 => '/radio/player/bbc_radio_two_country',

        'bbc_afrique_radio'                     => '/afrique/bbc_afrique_radio/liveradio',
        'bbc_gahuza_radio'                      => '/gahuza/bbc_gahuza_radio/liveradio',
        'bbc_hausa_radio'                       => '/hausa/bbc_hausa_radio/liveradio',
        'bbc_somali_radio'                      => '/somali/bbc_somali_radio/liveradio',
        'bbc_swahili_radio'                     => '/swahili/bbc_swahili_radio/liveradio',

        'bbc_afghan_radio'                      => null,
        'bbc_cantonese_radio'                   => null,

        'bbc_russian_radio'                     => '/russian/bbc_russian_radio/liveradio',
        'bbc_persian_radio'                     => '/persian/bbc_persian_radio/liveradio',
        'bbc_dari_radio'                        => '/persian/bbc_dari_radio/liveradio',
        'bbc_pashto_radio'                      => '/pashto/bbc_pashto_radio/liveradio',
        'bbc_arabic_radio'                      => '/arabic/bbc_arabic_radio/liveradio',
        'bbc_uzbek_radio'                       => '/uzbek/bbc_uzbek_radio/liveradio',
        'bbc_kyrgyz_radio'                      => '/kyrgyz/bbc_kyrgyz_radio/liveradio',
        'bbc_urdu_radio'                        => '/urdu/bbc_urdu_radio/liveradio',
        'bbc_burmese_radio'                     => '/burmese/bbc_burmese_radio/liveradio',
        'bbc_hindi_radio'                       => '/hindi/bbc_hindi_radio/liveradio',
        'bbc_bangla_radio'                      => '/bengali/bbc_bangla_radio/liveradio',
        'bbc_nepali_radio'                      => '/nepali/bbc_nepali_radio/liveradio',
        'bbc_tamil_radio'                       => '/tamil/bbc_tamil_radio/liveradio',
        'bbc_sinhala_radio'                     => '/sinhala/bbc_sinhala_radio/liveradio',

        'bbc_indonesian_radio'                  => '/indonesia/bbc_indonesian_radio/liveradio',

        'bbc_radio_solent_west_dorset'          => '/radio/player/bbc_radio_solent_west_dorset',
        'bbc_radio_scotland_mw'                 => '/radio/player/bbc_radio_scotland_mw',
        'bbc_world_service_americas'            => '/radio/player/bbc_world_service_americas',
        'bbc_world_service_australasia'         => '/radio/player/bbc_world_service_australasia',
        'bbc_world_service_east_africa'         => '/radio/player/bbc_world_service_east_africa',
        'bbc_world_service_east_asia'           => '/radio/player/bbc_world_service_east_asia',
        'bbc_world_service_europe'              => '/radio/player/bbc_world_service_europe',
        'bbc_world_service_south_asia'          => '/radio/player/bbc_world_service_south_asia',
        'bbc_world_service_west_africa'         => '/radio/player/bbc_world_service_west_africa',
        'cbeebies_radio'                        => '/radio/player/cbeebies_radio',
        'bbc_radio_two_eurovision'              => '/radio/player/bbc_radio_two_eurovision',
    ];

    /** @var Chronos */
    private $now;

    /** @var Chronos */
    private $sixMinutesFromNow;

    public function simulcastUrl(CollapsedBroadcast $collapsedBroadcast, ?Service $preferredService = null): string
    {
        $servicesBySid = $this->getServicesBySid($collapsedBroadcast);
        if ($preferredService) {
            $preferredServiceId = (string) $preferredService->getSid();
            if (isset($servicesBySid[$preferredServiceId]) && isset(self::LIVE_SERVICE_URLS[$preferredServiceId])) {
                return self::LIVE_SERVICE_URLS[$preferredServiceId];
            }
        }
        // Go through our list in order. We prefer default service (e.g. bbc_one_london) over regional ones etc.
        foreach (self::LIVE_SERVICE_URLS as $sid => $url) {
            if (isset($servicesBySid[$sid])) {
                return $url;
            }
        }

        return '';
    }

    public function isWatchableLive(CollapsedBroadcast $collapsedBroadcast, bool $advancedLive = false): bool
    {
        if ($collapsedBroadcast->isBlanked() || !$this->simulcastUrl($collapsedBroadcast)) {
            return false;
        }

        return $this->isOnNowIsh($collapsedBroadcast, $advancedLive);
    }

    private function isOnNowIsh(CollapsedBroadcast $collapsedBroadcast, bool $advancedLive = false): bool
    {
        $startBefore = $endAfter = $this->getNow();
        if ($advancedLive) {
            // This is used to show a link to a live broadcast before it starts
            // (caching etc)
            $startBefore = $this->getSixMinutesFromNow();
        }
        if ($collapsedBroadcast->getStartAt() <= $startBefore && $endAfter < $collapsedBroadcast->getEndAt()) {
            return true;
        }
        return false;
    }

    private function getServicesBySid(CollapsedBroadcast $collapsedBroadcast): array
    {
        $services = $collapsedBroadcast->getServices();
        $servicesBySid = [];
        foreach ($services as $service) {
            $servicesBySid[(string) $service->getSid()] = $service;
        }
        return $servicesBySid;
    }

    /**
     * This is going to be a lot quicker than creating a bunch of new date objects. Stop looking at me like that.
     */
    private function getNow(): Chronos
    {
        if (!$this->now) {
            $this->now = Chronos::now();
        }
        return $this->now;
    }

    private function getSixMinutesFromNow(): Chronos
    {
        if (!$this->sixMinutesFromNow) {
            $this->sixMinutesFromNow = $this->getNow()->addMinutes(6);
        }
        return $this->sixMinutesFromNow;
    }
}
