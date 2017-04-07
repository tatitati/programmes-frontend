<?php
declare(strict_types = 1);
namespace App\Ds2014\Organism\Programme;

use App\Ds2014\Presenter;
use App\Ds2014\PresenterFactory;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;

class ProgrammePresenter extends Presenter
{
    protected $options = [
        'showSynosis' => true,
    ];

    /** @var Programme */
    private $programme;

    public function __construct(
        PresenterFactory $ds2014,
        Programme $programme,
        array $options = []
    ) {
        parent::__construct($ds2014, $options);
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
        return $this->presenterFactory->getLocale();
    }
}
