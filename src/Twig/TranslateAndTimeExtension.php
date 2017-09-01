<?php
declare(strict_types = 1);
namespace App\Twig;

use App\Translate\TranslatableTrait;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Twig_Extension;
use Twig_Function;
use Twig_SimpleFilter;

/**
 * The local time functions make use of Translate fairly heavily.
 * Hence grouping local time and translation together.
 */
class TranslateAndTimeExtension extends Twig_Extension
{
    use TranslatableTrait;

    public function __construct(TranslateProvider $translateProvider)
    {
        $this->translateProvider = $translateProvider;
    }

    /**
     * @return Twig_SimpleFilter[]
     */
    public function getFilters(): array
    {
        return [
            new Twig_SimpleFilter('ucwords', 'ucwords'),
            new Twig_SimpleFilter('local_date_intl', [$this, 'localDateIntlWrapper']),
            new Twig_SimpleFilter('local_date', [$this, 'localDate']),
            new Twig_SimpleFilter('time_zone_note', [$this, 'timeZoneNote'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_Function('tr', [$this, 'trWrapper']),
        ];
    }

    public function trWrapper(
        string $key,
        $substitutions = [],
        $numPlurals = null,
        ?string $domain = null
    ): string {
        return $this->tr($key, $substitutions, $numPlurals, $domain);
    }

    public function localDateIntlWrapper(DateTimeInterface $dateTime, string $format): string
    {
        return $this->localDateIntl($dateTime, $format);
    }

    public function timeZoneNote(DateTimeInterface $dateTime): string
    {
        $tz = ApplicationTime::getLocalTimeZone();
        if ($tz->getName() === 'Europe/London') {
            // Displayed times are assumed to be UK unless otherwise stated
            return '';
        }
        $text = $this->tr('gmt');
        if ($tz->getName() !== 'UTC') {
            $dateTime = $this->toTimeZone($dateTime, new DateTimeZone('UTC'));
            $offset = $tz->getOffset($dateTime);
            $sign = ($offset > 0) ? '+' : '-';
            // Offset is set to positive to make the calculation easier (floor vs ceil etc)
            $offset = abs($offset);
            $hours = floor($offset / 3600);
            $minutes = floor(($offset - ($hours * 3600)) / 60);
            $text .= sprintf('%s%02d:%02d', $sign, $hours, $minutes);
        }

        return '<span class="timezone--note">' . $text . '</span>';
    }

    /**
     * This is much simpler than localDateIntl. It doesn't use INTL. It just formats
     * for the local timezone.
     */
    public function localDate(DateTimeInterface $dateTime, string $format)
    {
        $dateTime = $this->toTimeZone($dateTime, ApplicationTime::getLocalTimeZone());
        return $dateTime->format($format);
    }

    private function toTimeZone(DateTimeInterface $dateTime, DateTimeZone $timeZone): DateTimeInterface
    {
        if ($dateTime->getTimezone()->getName() !== $timeZone->getName()) {
            if (!$dateTime instanceof DateTimeImmutable) {
                $dateTime = clone $dateTime;
            }
            $dateTime = $dateTime->setTimezone($timeZone);
        }
        return $dateTime;
    }
}
