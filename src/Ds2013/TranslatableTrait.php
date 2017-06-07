<?php
declare(strict_types = 1);
namespace App\Ds2013;

use DateTimeInterface;
use IntlDateFormatter;
use RMP\Translate\Translate;

trait TranslatableTrait
{
    /** @var Translate */
    protected $translate;

    /**
     * @param DateTimeInterface $dateTime
     * @param string $format    Format must be in ICU format
     *
     * @see http://userguide.icu-project.org/formatparse/datetime
     *
     * @return bool|string
     */
    protected function dateFormat(DateTimeInterface $dateTime, string $format)
    {
        $formatter = IntlDateFormatter::create(
            $this->translate->getLocale(),
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

        return $this->translate->translate($key, $substitutions, $numPlurals, $domain);
    }
}
