<?php
declare(strict_types = 1);
namespace App\Translate;

use DateTimeInterface;
use IntlDateFormatter;

trait TranslatableTrait
{
    /** @var TranslateProvider */
    protected $translateProvider;

    /**
     * @param DateTimeInterface $dateTime
     * @param string $format    Format must be in ICU format
     *
     * @see http://userguide.icu-project.org/formatparse/datetime
     *
     * @return bool|string
     */
    protected function localDate(DateTimeInterface $dateTime, string $format)
    {
        $formatter = IntlDateFormatter::create(
            $this->translateProvider->getTranslate()->getLocale(),
            IntlDateFormatter::NONE,
            IntlDateFormatter::NONE,
            $dateTime->getTimezone(),
            IntlDateFormatter::GREGORIAN,
            $format
        );

        return $formatter->format($dateTime->getTimestamp());
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
