<?php
declare(strict_types = 1);
namespace App\Translate;

use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use DateTimeInterface;
use DateTimeZone;
use IntlDateFormatter;

trait TranslatableTrait
{
    /** @var TranslateProvider */
    protected $translateProvider;

    /**
     * Formatter for international dates in a "Text" format. Need something with translated words in it?
     * You want this one
     *
     * @param DateTimeInterface $dateTime
     * @param string $format    Format must be in ICU format
     *
     * @see http://userguide.icu-project.org/formatparse/datetime
     *
     * @return bool|string
     */
    protected function localDateIntl(DateTimeInterface $dateTime, string $format, DateTimeZone $timeZone = null)
    {
        if (!$timeZone) {
            $timeZone = ApplicationTime::getLocalTimeZone();
        }
        $locale = $this->translateProvider->getTranslate()->getLocale();
        $formatter = IntlDateFormatter::create(
            $locale,
            IntlDateFormatter::LONG,
            IntlDateFormatter::NONE,
            $timeZone,
            IntlDateFormatter::GREGORIAN,
            $format
        );

        $output = $formatter->format($dateTime->getTimestamp());
        //@TODO figure out if we need RMP\Translate's DateCorrection or if our OS now correctly handles these spellings
        /*
        $dateCorrection = new DateCorrection();
        $output = $dateCorrection->fixSpelling($output, $this->translateProvider->getTranslate()->getLocale());
        */
        return $output;
    }

    protected function tr(
        string $key,
        $substitutions = [],
        $numPlurals = null,
        ?string $domain = null
    ): string {
        if (is_int($substitutions) && is_null($numPlurals)) {
            $numPlurals = $substitutions;
            $substitutions = array('%count%' => $numPlurals);
        }

        if (is_int($numPlurals) && !isset($substitutions['%count%'])) {
            $substitutions['%count%'] = $numPlurals;
        }

        return $this->translateProvider->getTranslate()->translate($key, $substitutions, $numPlurals, $domain);
    }
}
