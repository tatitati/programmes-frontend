<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Map\SubPresenter;

use App\DsAmen\Presenter;
use App\Exception\InvalidOptionException;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Promotion;

class ComingSoonPresenter extends Presenter
{
    /** @var Programme */
    private $programme;

    /** @var Promotion|null */
    private $promotion;

    /** @var bool */
    private $showMiniMap;

    /** @var bool */
    private $showSynopsis;

    public function __construct(Programme $programme, ?Promotion $promotion, array $options = [])
    {
        parent::__construct($options);
        $this->programme = $programme;
        $this->promotion = $promotion;
        $this->showMiniMap = $this->getOption('show_mini_map');
        $this->showSynopsis = $this->getOption('show_synopsis');
    }

    public function getProgramme(): Programme
    {
        return $this->programme;
    }

    public function getPromotion(): ?Promotion
    {
        return $this->promotion;
    }

    public function getComingSoonTextOnly(): string
    {
        return $this->programme->getOption('comingsoon_textonly');
    }

    public function showMiniMap(): bool
    {
        return $this->showMiniMap;
    }

    public function showSynopsis(): bool
    {
        return $this->showSynopsis;
    }

    protected function validateOptions(array $options): void
    {
        if (!is_bool($options['show_mini_map'])) {
            throw new InvalidOptionException('show_mini_map option must be a boolean');
        }

        if (!is_bool($options['show_synopsis'])) {
            throw new InvalidOptionException('show_synopsis option must be a boolean');
        }
    }
}
