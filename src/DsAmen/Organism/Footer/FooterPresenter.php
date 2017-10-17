<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Footer;

use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Format;
use BBC\ProgrammesPagesService\Domain\Entity\Genre;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;

class FooterPresenter extends Presenter
{
    /** @var  Programme */
    private $programme;

    /** @var Network */
    private $network;

    public function __construct(Programme $programme, array $options = [])
    {
        parent::__construct($options);

        $this->programme = $programme;
        $this->network = $programme->getNetwork();
    }

    public function hasNetwork(): bool
    {
        return (bool) $this->network;
    }

    public function getNetworkUrlKey(): string
    {
        return $this->network ? $this->network->getUrlKey() : "";
    }

    public function getNetworkName(): string
    {
        return $this->network ? $this->network->getName() : "";
    }

    /**
     * @return string[][]
     */
    public function getNavigationLinks(): array
    {
        return $this->network->getOption('navigation_links');
    }

    public function getNid(): string
    {
        return (string) $this->network->getNid();
    }

    public function getNetworkImageUrl(): string
    {
        return $this->network->getImage()->getUrl(112, 'n');
    }

    /**
     * @return Genre[]
     */
    public function getGenres(): array
    {
        $genres = $this->programme->getGenres();

        usort($genres, function (Genre $a, Genre $b) {
            return  $a->getUrlKeyHierarchy() <=> $b->getUrlKeyHierarchy();
        });

        return $genres;
    }

    /**
     * @return Format[]
     */
    public function getFormats(): array
    {
        $formats = $this->programme->getFormats();

        usort($formats, function (Format $a, Format $b) {
            return  $a->getUrlKey() <=> $b->getUrlKey();
        });

        return $formats;
    }
}
