<?php
declare(strict_types = 1);

namespace App\DsShared\Helpers;

use App\Translate\TranslateProvider;
use DateTime;
use IntlDateFormatter;
use RMP\Translate\DateCorrection;

/**
 * View helper - Creates complete lists of localised days of the week and months
 * Available collators in PHP:
 * http://stackoverflow.com/questions/9422553/list-of-available-collators-in-php/9422745#9422745
 *
 * @author BBC Programmes Developers <programmes-devel@lists.forge.bbc.co.uk>
 * @copyright Copyright (c) 2015 BBC (http://www.bbc.co.uk)
 *
 */
class LocalisedDaysAndMonthsHelper
{
    /** @var TranslateProvider */
    protected $translateProvider;

    public function __construct(TranslateProvider $translateProvider)
    {
        $this->translateProvider = $translateProvider;
    }

    public function localisedDaysAndMonths(): string
    {
        $localisedDaysAndMonths = [];
        $dateHelper = new DateCorrection();

        $locale = $this->translateProvider->getLocale();
        $fmt = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            'GMT'
        );
        $date = new DateTime();
        $date->setTime(6, 30);

        $localisedDaysAndMonths['days'] = [];
        $fmt->setPattern('EEE');
        foreach (range(1, 7) as $dayNumber) {
            $date->setDate(2015, 3, $dayNumber);
            $string = $fmt->format($date->getTimestamp());
            $string = $dateHelper->fixSpelling($string, $locale);
            $localisedDaysAndMonths['days'][] = $string;
        }

        $localisedDaysAndMonths['months'] = [];
        $fmt->setPattern('MMM');
        foreach (range(1, 12) as $monthNumber) {
            $date->setDate(2015, $monthNumber, 1);
            $string = $fmt->format($date->getTimestamp());
            $string = $dateHelper->fixSpelling($string, $locale);
            $localisedDaysAndMonths['months'][] = $string;
        }

        return json_encode($localisedDaysAndMonths);
    }
}
