<?php
declare(strict_types = 1);
namespace App\Ds2013\Organism\Programme;

use App\Ds2013\Presenter;
use App\Ds2013\TranslatableTrait;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use RMP\Translate\Translate;

class ProgrammePresenter extends Presenter
{
    use TranslatableTrait;

    protected $options = [
        'showSynosis' => true,
    ];

    /** @var Programme */
    private $programme;

    public function __construct(
        Translate $translate,
        Programme $programme,
        array $options = []
    ) {
        parent::__construct($options);
        $this->translate = $translate;
        $this->programme = $programme;
    }

    public function getTitle(): string
    {
        return $this->programme->getTitle();
    }

    public function getSynopsis(): string
    {
        return $this->programme->getShortSynopsis();
    }

    public function showSynopsis(): bool
    {
        return $this->options['showSynopsis'];
    }

    public function getLocale(): string
    {
        return $this->translate->getLocale();
    }
}
