<?php
declare(strict_types = 1);
namespace App\DsShared\Helpers;

use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Chronos;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LiveBroadcastHelper
{
    private const LIVE_SERVICE_URLS = [
        //sid                                   => [route, [...arguments]],
        'bbc_one_london'                        => ['iplayer_live', ['sid' => 'bbcone']],
        'bbc_one_scotland'                      => ['iplayer_live', ['sid' => 'bbcone', 'area' => 'scotland']],
        'bbc_one_wales'                         => ['iplayer_live', ['sid' => 'bbcone', 'area' => 'wales']],
        'bbc_one_northern_ireland'              => ['iplayer_live', ['sid' => 'bbcone', 'area' => 'northern_ireland']],
        'bbc_one_cambridge'                     => ['iplayer_live', ['sid' => 'bbcone', 'area' => 'cambridge']],
        'bbc_one_channel_islands'               => ['iplayer_live', ['sid' => 'bbcone', 'area' => 'channel_islands']],
        'bbc_one_east'                          => ['iplayer_live', ['sid' => 'bbcone', 'area' => 'east']],
        'bbc_one_east_midlands'                 => ['iplayer_live', ['sid' => 'bbcone', 'area' => 'east_midlands']],
        'bbc_one_east_yorkshire'                => ['iplayer_live', ['sid' => 'bbcone', 'area' => 'east_yorkshire']],
        'bbc_one_north_east'                    => ['iplayer_live', ['sid' => 'bbcone', 'area' => 'north_east']],
        'bbc_one_north_west'                    => ['iplayer_live', ['sid' => 'bbcone', 'area' => 'north_west']],
        'bbc_one_oxford'                        => ['iplayer_live', ['sid' => 'bbcone', 'area' => 'oxford']],
        'bbc_one_south'                         => ['iplayer_live', ['sid' => 'bbcone', 'area' => 'south']],
        'bbc_one_south_east'                    => ['iplayer_live', ['sid' => 'bbcone', 'area' => 'south_east']],
        'bbc_one_south_west'                    => ['iplayer_live', ['sid' => 'bbcone', 'area' => 'south_west']],
        'bbc_one_west'                          => ['iplayer_live', ['sid' => 'bbcone', 'area' => 'west']],
        'bbc_one_west_midlands'                 => ['iplayer_live', ['sid' => 'bbcone', 'area' => 'west_midlands']],
        'bbc_one_yorks'                         => ['iplayer_live', ['sid' => 'bbcone', 'area' => 'yorks']],
        'bbc_two_england'                       => ['iplayer_live', ['sid' => 'bbctwo']],
        // @TODO are these correct for bbc_two regions
        'bbc_two_northern_ireland_digital'      => ['iplayer_live', ['sid' => 'bbctwo', 'area' => 'northern_ireland_digital']],
        'bbc_two_wales_digital'                 => ['iplayer_live', ['sid' => 'bbctwo', 'area' => 'wales_digital']],
        'bbc_two_scotland'                      => ['iplayer_live', ['sid' => 'bbctwo', 'area' => 'scotland']],
        'bbc_three'                             => ['iplayer_live', ['sid' => 'bbcthree']],
        'bbc_four'                              => ['iplayer_live', ['sid' => 'bbcfour']],
        'cbbc'                                  => ['iplayer_live', ['sid' => 'cbbc']],
        'cbeebies'                              => ['iplayer_live', ['sid' => 'cbeebies']],
        'bbc_news24'                            => ['iplayer_live', ['sid' => 'bbcnews']],
        'bbc_parliament'                        => ['iplayer_live', ['sid' => 'bbcparliament']],
        'bbc_alba'                              => ['iplayer_live', ['sid' => 'bbcalba']],

        'bbc_radio_one'                         => ['network', ['networkUrlKey' => 'radio1']],
        'bbc_radio_one_vintage'                 => ['network', ['networkUrlKey' => 'radio1vintage']],
        'bbc_1xtra'                             => ['network', ['networkUrlKey' => '1xtra']],
        'bbc_radio_two'                         => ['network', ['networkUrlKey' => 'radio2']],
        'bbc_radio_three'                       => ['network', ['networkUrlKey' => 'radio3']],
        'bbc_radio_fourfm'                      => ['network', ['networkUrlKey' => 'radio4']],
        'bbc_radio_fourlw'                      => ['network', ['networkUrlKey' => 'radio4']],
        'bbc_radio_four_extra'                  => ['network', ['networkUrlKey' => 'radio4extra']],
        'bbc_radio_five_live'                   => ['network', ['networkUrlKey' => '5live']],
        'bbc_radio_five_live_sports_extra'      => ['network', ['networkUrlKey' => '5livesportsextra']],
        'bbc_6music'                            => ['network', ['networkUrlKey' => '6music']],
        'bbc_7'                                 => ['network', ['networkUrlKey' => 'radio4extra']],
        'bbc_asian_network'                     => ['network', ['networkUrlKey' => 'asiannetwork']],
        'bbc_world_service'                     => ['network', ['networkUrlKey' => 'worldserviceradio']],
        'bbc_radio_scotland_fm'                 => ['network', ['networkUrlKey' => 'radioscotland']],
        'bbc_radio_nan_gaidheal'                => ['network', ['networkUrlKey' => 'radionangaidheal']],
        'bbc_radio_ulster'                      => ['network', ['networkUrlKey' => 'radioulster']],
        'bbc_radio_foyle'                       => ['network', ['networkUrlKey' => 'radiofoyle']],
        'bbc_radio_wales_fm'                    => ['network', ['networkUrlKey' => 'radiowales']],
        'bbc_radio_cymru'                       => ['network', ['networkUrlKey' => 'radiocymru']],
        'bbc_radio_cymru_mwy'                   => ['network', ['networkUrlKey' => 'radiocymrumwy']],
        'bbc_london'                            => ['network', ['networkUrlKey' => 'radiolondon']],
        'bbc_radio_berkshire'                   => ['network', ['networkUrlKey' => 'radioberkshire']],
        'bbc_radio_bristol'                     => ['network', ['networkUrlKey' => 'radiobristol']],
        'bbc_radio_cambridge'                   => ['network', ['networkUrlKey' => 'radiocambridgeshire']],
        'bbc_radio_cornwall'                    => ['network', ['networkUrlKey' => 'radiocornwall']],
        'bbc_radio_coventry_warwickshire'       => ['network', ['networkUrlKey' => 'bbccoventryandwarwickshire']],
        'bbc_radio_cumbria'                     => ['network', ['networkUrlKey' => 'radiocumbria']],
        'bbc_radio_derby'                       => ['network', ['networkUrlKey' => 'radioderby']],
        'bbc_radio_devon'                       => ['network', ['networkUrlKey' => 'radiodevon']],
        'bbc_radio_essex'                       => ['network', ['networkUrlKey' => 'bbcessex']],
        'bbc_radio_gloucestershire'             => ['network', ['networkUrlKey' => 'radiogloucestershire']],
        'bbc_radio_guernsey'                    => ['network', ['networkUrlKey' => 'radioguernsey']],
        'bbc_radio_hereford_worcester'          => ['network', ['networkUrlKey' => 'bbcherefordandworcester']],
        'bbc_radio_humberside'                  => ['network', ['networkUrlKey' => 'radiohumberside']],
        'bbc_radio_jersey'                      => ['network', ['networkUrlKey' => 'radiojersey']],
        'bbc_radio_kent'                        => ['network', ['networkUrlKey' => 'radiokent']],
        'bbc_radio_lancashire'                  => ['network', ['networkUrlKey' => 'radiolancashire']],
        'bbc_radio_leeds'                       => ['network', ['networkUrlKey' => 'radioleeds']],
        'bbc_radio_leicester'                   => ['network', ['networkUrlKey' => 'radioleicester']],
        'bbc_radio_lincolnshire'                => ['network', ['networkUrlKey' => 'radiolincolnshire']],
        'bbc_radio_manchester'                  => ['network', ['networkUrlKey' => 'radiomanchester']],
        'bbc_radio_merseyside'                  => ['network', ['networkUrlKey' => 'radiomerseyside']],
        'bbc_radio_newcastle'                   => ['network', ['networkUrlKey' => 'bbcnewcastle']],
        'bbc_radio_norfolk'                     => ['network', ['networkUrlKey' => 'radionorfolk']],
        'bbc_radio_northampton'                 => ['network', ['networkUrlKey' => 'radionorthampton']],
        'bbc_radio_nottingham'                  => ['network', ['networkUrlKey' => 'radionottingham']],
        'bbc_radio_oxford'                      => ['network', ['networkUrlKey' => 'radiooxford']],
        'bbc_radio_sheffield'                   => ['network', ['networkUrlKey' => 'radiosheffield']],
        'bbc_radio_shropshire'                  => ['network', ['networkUrlKey' => 'radioshropshire']],
        'bbc_radio_solent'                      => ['network', ['networkUrlKey' => 'radiosolent']],
        'bbc_radio_somerset_sound'              => ['network', ['networkUrlKey' => 'bbcsomerset']],
        'bbc_radio_stoke'                       => ['network', ['networkUrlKey' => 'radiostoke']],
        'bbc_radio_suffolk'                     => ['network', ['networkUrlKey' => 'radiosuffolk']],
        'bbc_radio_surrey'                      => ['network', ['networkUrlKey' => 'bbcsurrey']],
        'bbc_radio_sussex'                      => ['network', ['networkUrlKey' => 'bbcsussex']],
        'bbc_radio_swindon'                     => ['network', ['networkUrlKey' => 'swindon']],
        'bbc_radio_wiltshire'                   => ['network', ['networkUrlKey' => 'bbcwiltshire']],
        'bbc_radio_york'                        => ['network', ['networkUrlKey' => 'radioyork']],
        'bbc_southern_counties_radio'           => ['network', ['networkUrlKey' => 'southerncounties']],
        'bbc_tees'                              => ['network', ['networkUrlKey' => 'bbctees']],
        'bbc_three_counties_radio'              => ['network', ['networkUrlKey' => 'threecountiesradio']],
        'bbc_wm'                                => ['network', ['networkUrlKey' => 'wm']],
        'bbc_music_jazz'                        => ['network', ['networkUrlKey' => 'musicjazz']],
        'bbc_radio_two_fifties'                 => ['network', ['networkUrlKey' => 'radio250s']],
        'bbc_radio_two_country'                 => ['network', ['networkUrlKey' => 'radio2country']],

        'bbc_afrique_radio'                     => ['worldservice_liveradio', ['language' => 'afrique', 'sid' => 'bbc_afrique_radio']],
        'bbc_gahuza_radio'                      => ['worldservice_liveradio', ['language' => 'gahuza', 'sid' => 'bbc_gahuza_radio']],
        'bbc_hausa_radio'                       => ['worldservice_liveradio', ['language' => 'hausa', 'sid' => 'bbc_hausa_radio']],
        'bbc_somali_radio'                      => ['worldservice_liveradio', ['language' => 'somali', 'sid' => 'bbc_somali_radio']],
        'bbc_swahili_radio'                     => ['worldservice_liveradio', ['language' => 'swahili', 'sid' => 'bbc_swahili_radio']],

        'bbc_afghan_radio'                      => null,
        'bbc_cantonese_radio'                   => null,

        'bbc_russian_radio'                     => ['worldservice_liveradio', ['language' => 'russian', 'sid' => 'bbc_russian_radio']],
        'bbc_persian_radio'                     => ['worldservice_liveradio', ['language' => 'persian', 'sid' => 'bbc_persian_radio']],
        'bbc_dari_radio'                        => ['worldservice_liveradio', ['language' => 'persian', 'sid' => 'bbc_dari_radio']],
        'bbc_pashto_radio'                      => ['worldservice_liveradio', ['language' => 'pashto', 'sid' => 'bbc_pashto_radio']],
        'bbc_arabic_radio'                      => ['worldservice_liveradio', ['language' => 'arabic', 'sid' => 'bbc_arabic_radio']],
        'bbc_uzbek_radio'                       => ['worldservice_liveradio', ['language' => 'uzbek', 'sid' => 'bbc_uzbek_radio']],
        'bbc_kyrgyz_radio'                      => ['worldservice_liveradio', ['language' => 'kyrgyz', 'sid' => 'bbc_kyrgyz_radio']],
        'bbc_urdu_radio'                        => ['worldservice_liveradio', ['language' => 'urdu', 'sid' => 'bbc_urdu_radio']],
        'bbc_burmese_radio'                     => ['worldservice_liveradio', ['language' => 'burmese', 'sid' => 'bbc_burmese_radio']],
        'bbc_hindi_radio'                       => ['worldservice_liveradio', ['language' => 'hindi', 'sid' => 'bbc_hindi_radio']],
        'bbc_bangla_radio'                      => ['worldservice_liveradio', ['language' => 'bengali', 'sid' => 'bbc_bangla_radio']],
        'bbc_nepali_radio'                      => ['worldservice_liveradio', ['language' => 'nepali', 'sid' => 'bbc_nepali_radio']],
        'bbc_tamil_radio'                       => ['worldservice_liveradio', ['language' => 'tamil', 'sid' => 'bbc_tamil_radio']],
        'bbc_sinhala_radio'                     => ['worldservice_liveradio', ['language' => 'sinhala', 'sid' => 'bbc_sinhala_radio']],

        'bbc_indonesian_radio'                  => ['worldservice_liveradio', ['language' => 'indonesia', 'sid' => 'bbc_indonesian_radio']],

        'bbc_radio_cymru_2'                     => ['playspace_player', ['networkUrlKey' => 'radiocymru2']],
        'bbc_radio_solent_west_dorset'          => ['popup_player', ['sid' => 'bbc_radio_solent_west_dorset']],
        'bbc_radio_scotland_mw'                 => ['network', ['networkUrlKey' => 'radioscotland']],
        'bbc_world_service_americas'            => ['network', ['networkUrlKey' => 'worldserviceradio']],
        'bbc_world_service_australasia'         => ['network', ['networkUrlKey' => 'worldserviceradio']],
        'bbc_world_service_east_africa'         => ['network', ['networkUrlKey' => 'worldserviceradio']],
        'bbc_world_service_east_asia'           => ['network', ['networkUrlKey' => 'worldserviceradio']],
        'bbc_world_service_europe'              => ['network', ['networkUrlKey' => 'worldserviceradio']],
        'bbc_world_service_south_asia'          => ['network', ['networkUrlKey' => 'worldserviceradio']],
        'bbc_world_service_west_africa'         => ['network', ['networkUrlKey' => 'worldserviceradio']],
        'cbeebies_radio'                        => ['network', ['networkUrlKey' => 'cbeebiesradio']],
        'bbc_radio_two_eurovision'              => ['network', ['networkUrlKey' => 'radio2eurovision']],
    ];

    /** @var Chronos */
    private $now;

    /** @var Chronos */
    private $sixMinutesFromNow;

    /** @var UrlGeneratorInterface */
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function simulcastUrl(
        CollapsedBroadcast $collapsedBroadcast,
        ?Service $preferredService = null,
        array $additionalUrlParameters = []
    ): string {
        $liveServiceSid = $this->calculateLiveServiceSid($collapsedBroadcast, $preferredService);
        if (!$liveServiceSid) {
            return '';
        }
        $parameters = self::LIVE_SERVICE_URLS[$liveServiceSid];
        $params = array_merge($parameters[1], $additionalUrlParameters);
        return $this->router->generate(
            $parameters[0],
            $params,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function isWatchableLive(CollapsedBroadcast $collapsedBroadcast, bool $advancedLive = false): bool
    {
        if ($collapsedBroadcast->isBlanked() || !$this->calculateLiveServiceSid($collapsedBroadcast)) {
            return false;
        }

        return $this->isOnNowIsh($collapsedBroadcast, $advancedLive);
    }

    private function isOnNowIsh(CollapsedBroadcast $collapsedBroadcast, bool $advancedLive = false): bool
    {
        $startBefore = $endAfter = ApplicationTime::getTime();
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

    private function getSixMinutesFromNow(): Chronos
    {
        if (!$this->sixMinutesFromNow) {
            $this->sixMinutesFromNow = ApplicationTime::getTime()->addMinutes(6);
        }
        return $this->sixMinutesFromNow;
    }

    private function calculateLiveServiceSid(
        CollapsedBroadcast $collapsedBroadcast,
        ?Service $preferredService = null
    ): ?string {
        $servicesBySid = $this->getServicesBySid($collapsedBroadcast);
        if ($preferredService) {
            $preferredServiceId = (string) $preferredService->getSid();
            if (isset($servicesBySid[$preferredServiceId]) && isset(self::LIVE_SERVICE_URLS[$preferredServiceId])) {
                return $preferredServiceId;
            }
        }
        // Go through our list in order. We prefer default service (e.g. bbc_one_london) over regional ones etc.
        foreach (self::LIVE_SERVICE_URLS as $sid => $parameters) {
            if (isset($servicesBySid[$sid])) {
                return $sid;
            }
        }

        return null;
    }
}
