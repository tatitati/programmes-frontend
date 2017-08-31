<?php
declare(strict_types = 1);

namespace App\ValueObject;

use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Service;

class IstatsAnalyticsLabels
{
    /** @var string[] */
    private $labels = [];

    /** @var string[] */
    private $orbLabels = [];

    public function __construct($context, string $progsPageType, string $appVersion, ?array $extraLabels)
    {
        $this->labels = [
            'app_name'           => 'programmes',
            'prod_name'          => 'programmes',
            // In V2 progs_page_type is "controller + action" but in V3 is only the controller name.
            // A regex is used on iStats page to map V2 and V3 values
            'app_version'        => $appVersion,
        ];

        $this->setProgsPageTypeLabel($progsPageType);
        $this->setProgrammeLabels($context);
        $this->setBbcSiteLabel($context);
        $this->setEventMasterBrand($context);

        if (!empty($extraLabels)) {
            $this->labels = array_merge($this->labels, $extraLabels);
        }
    }

    public function orbLabels(): array
    {
        // The ORB Mustache template wants the labels in a slightly clunky format. We should oblige it.
        if (empty($this->orbLabels)) {
            foreach ($this->labels as $key => $value) {
                $this->orbLabels[] = ['key' => $key, 'value' => urlencode($value)];
            }
        }

        return $this->orbLabels;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * progs_page_type is a iStat label which in V2 is generated based on controllers name + action. V3 has different
     * controller names and we want to keep same value so we need to map our controllers with the value generated on V2.
     *
     * If this isn't set in a controller, BaseController will set the controller name as the value.
     * If a controller set the value to an empty string, this label won't be sent to iStats.
     */
    private function setProgsPageTypeLabel(string $progsPageType)
    {
        if ($progsPageType !== '') {
            $this->labels['progs_page_type'] = $progsPageType;
        }
    }

    private function setProgrammeLabels($context): void
    {
        if ($context instanceof Programme) {
            $this->labels['programme_title'] = $this->getConcatenatedAncestryTitles($context);
            $this->labels['brand_title'] = $context->getTleo()->getTitle();
            $this->labels['pips_genre_group_ids'] = $this->getGenreLabel($context);
            $this->setDynamicLabels($context);
            // rec_* labels are for RecEng which supplies the "You may also like" suggestions for iPlayer and /Programmes
            $this->labels['rec_v'] = '2'; // The version of RecEng being used
            $this->labels['rec_app_id'] = 'programmes'; // The app id of the client calling RecEng
            $this->labels['rec_p'] = $this->getMediaTypeLabel($context);
        }
    }

    /**
     * This function uses recursion to climb up the parents tree and return a concatenation of the titles
     */
    private function getConcatenatedAncestryTitles(?Programme $context): string
    {
        $titlesConcatenation = implode(', ', array_map(function ($ancestry) {
            return $ancestry->getTitle();
        }, array_reverse($context->getAncestry())));
        return $titlesConcatenation;
    }

    /**
     * Returns The PIDs of the different genres the programme belongs to, concatenated
     */
    private function getGenreLabel(Programme $context): string
    {
        $genreLabel = implode(', ', array_map(function ($genre) {
            return $genre->getId();
        }, $context->getGenres()));
        return $genreLabel;
    }

    private function getMediaTypeLabel(Programme $context): string
    {
        $mediaType = 'null';

        if ($context instanceof ProgrammeItem) {
            $mediaType = ($context->getMediaType() == 'audio') ? 'audio' : 'video';
        }

        return $mediaType . '_null_2';
    }

    private function setBbcSiteLabel($context): void
    {
        $specialCases = [
            // Masterbrand id => isite label
            'bbc_radio_one' => 'iplayerradio-radio1',
            'bbc_radio_two' => 'iplayerradio-radio2',
            'bbc_radio_three' => 'iplayerradio-radio3',
            'bbc_radio_four' => 'iplayerradio-radio4',
            'bbc_1xtra' => 'iplayerradio-1xtra',
            'bbc_radio_four_extra' => 'iplayerradio-radio4extra',
            'bbc_radio_five_live' => 'iplayerradio-5live',
            'bbc_radio_five_live_sports_extra' => 'iplayerradio-5livesportsextra',
            'bbc_6music' => 'iplayerradio-6music',
            'bbc_asian_network' => 'iplayerradio-asiannetwork',
            'bbc_radio_cymru' => 'iplayerradio-radiocymru',
            'bbc_radio_cymru_mwy' => 'iplayerradio-radiocymru',
            'bbc_radio_scotland' => 'iplayerradio-scotland',
            'bbc_radio_scotland_music_extra' => 'iplayerradio-scotland',
        ];

        if (($context instanceof CoreEntity || $context instanceof Service) && $context->getNetwork()) {
            $nid = (string) $context->getNetwork()->getNid();
            if ($nid === 'bbc_music') {
                $this->labels['bbc_site'] = 'music';
            } elseif ($nid === 'bbc_arts') {
                $this->labels['bbc_site'] = 'arts';
            } elseif ($context->getNetwork()->isTv()) {
                $this->labels['bbc_site'] = 'tvandiplayer';
            } elseif (isset($specialCases[$nid])) {
                $this->labels['bbc_site'] = $specialCases[$nid];
            } elseif ($context->getNetwork()->isRadio()) {
                $this->labels['bbc_site'] = 'iplayerradio';
            }
        }
    }

    private function setEventMasterBrand($context): void
    {
        if ($context instanceof CoreEntity && !empty($context->getMasterBrand())) {
            $this->labels['event_master_brand'] = (string) $context->getMasterBrand()->getMid();
        } elseif ($context instanceof Service && !empty($context->getNetwork())) {
            $this->labels['event_master_brand'] = (string) $context->getNetwork()->getNid();
        }
    }

    /**
     * Dynamic generated based on the ancestors
     */
    private function setDynamicLabels(?Programme $context): void
    {
        if (isset($context)) {
            $this->setDynamicLabels($context->getParent());
            $type = $context->getType();
            $this->labels[$type . '_id'] = (string) $context->getPid();
            $this->labels[$type . '_title'] = $context->getTitle();
        }
    }
}
