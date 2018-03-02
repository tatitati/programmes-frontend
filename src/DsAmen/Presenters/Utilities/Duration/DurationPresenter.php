<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Utilities\Duration;

use App\DsAmen\Presenter;
use App\Translate\TranslateProvider;

class DurationPresenter extends Presenter
{
    /** @var int */
    private $hours = 0;

    /** @var int */
    private $minutes = 0;

    /** @var int */
    private $seconds = 0;

    /** @var TranslateProvider */
    private $translateProvider;

    /** @var array */
    protected $options = [
        'h_title' => 'duration',
        'show_heading' => false,
    ];

    public function __construct(int $duration, TranslateProvider $translateProvider, array $options = [])
    {
        parent::__construct($options);
        $this->makeParts($duration);
        $this->translateProvider = $translateProvider;
    }

    public function getAriaLabel(): string
    {
        $ariaLabel = '';
        $tr = $this->translateProvider->getTranslate();

        if ($this->getHours()) {
            $ariaLabel .= $tr->translate('time_hours_long', ['%1' => $this->getHours()]);
        }

        if ($this->getMinutes()) {
            $ariaLabel .= ' ' . $tr->translate('time_minutes_long', ['%1' => $this->getMinutes()]);
        }

        if ($this->getSeconds()) {
            $ariaLabel .= ' ' . $tr->translate('time_seconds_long', ['%1' => $this->getSeconds()]);
        }

        return trim($ariaLabel);
    }

    public function getFormattedDuration(): string
    {
        if ($this->getHours() > 0) {
            // 1+ hours e.g. (1:23:45)
            return $this->getHours() . ":"
                . sprintf('%02d', $this->getMinutes()) . ":"
                . sprintf('%02d', $this->getSeconds());
        }

        if ($this->getMinutes() > 0) {
            // 1+ minutes e.g. (1:23)
            return $this->getMinutes() . ":"
                . sprintf('%02d', $this->getSeconds());
        }

        // Under 1 minute e.g. (0:12)
        return "0:" . sprintf('%02d', $this->getSeconds());
    }

    public function getHeadingClass(): string
    {
        return ($this->getOption('show_heading') ? '' : 'invisible');
    }

    public function getHours(): int
    {
        return $this->hours;
    }

    public function getMinutes(): int
    {
        return $this->minutes;
    }

    public function getSeconds(): int
    {
        return $this->seconds;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    private function makeParts(int $seconds): void
    {
        $this->hours = (int) floor($seconds / 3600);
        $this->minutes = (int) floor(($seconds / 60) % 60);
        $this->seconds = $seconds % 60;
    }
}
