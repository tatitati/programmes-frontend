<?php
declare(strict_types = 1);
namespace App\Ds2013\Organism\Programme;

use App\Ds2013\Presenter;
use App\Ds2013\PresenterFactory;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;

class ProgrammePresenter extends Presenter
{
    protected $options = [
        'showSynosis' => true,
    ];

    /** @var Programme */
    private $programme;

    public function __construct(
        PresenterFactory $ds2013,
        Programme $programme,
        array $options = []
    ) {
        parent::__construct($ds2013, $options);
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
        return $this->presenterFactory->getTranslate()->getLocale();
    }
}
