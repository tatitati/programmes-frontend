<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Programme\SubPresenters;

use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use Cake\Chronos\Date;

class ProgrammeBodyPresenter extends Presenter
{
    /** @var Programme */
    private $programme;

    /** @var  array */
    protected $options = [
        'show_synopsis' => false,
        'synopsis_class' => 'invisible visible@gel3',
        'show_release_date' => false,
        'full_details_class' => 'programme__details',
    ];

    /** @var Date|null  */
    protected $releaseDate = null;

    public function __construct(Programme $programme, array $options = [])
    {
        parent::__construct($options);
        $this->programme = $programme;

        if ($this->programme instanceof ProgrammeItem && $this->programme->getReleaseDate()) {
            $this->releaseDate = new Date((string) $this->programme->getReleaseDate());
        }
    }

    public function getReleaseDate(): ?Date
    {
        return $this->releaseDate;
    }

    public function getSynopsis(): string
    {
        return $this->programme->getShortSynopsis();
    }

    public function hasFullDetails(): bool
    {
        return $this->getOption('show_synopsis') || $this->hasReleaseDate();
    }

    public function hasReleaseDate(): bool
    {
        return $this->getOption('show_release_date') && !is_null($this->releaseDate);
    }
}
