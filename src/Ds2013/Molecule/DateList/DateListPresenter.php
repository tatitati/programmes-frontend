<?php
declare(strict_types = 1);

namespace App\Ds2013\Molecule\DateList;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Chronos;

class DateListPresenter extends Presenter
{
    /** @inheritDoc */
    protected $options = [
        'css_classes' => '',
        'user_timezone' => 'GMT',
    ];

    /** @var Chronos */
    private $datetime;

    /** @var Service */
    private $service;

    public function __construct(Chronos $datetime, Service $service, array $options = [])
    {
        parent::__construct($options);
        $this->datetime = $datetime;
        $this->service = $service;
    }

    public function getDateListItem(int $offset): DateListItemPresenter
    {
        return new DateListItemPresenter(
            $this->datetime,
            $this->service,
            $offset,
            ['user_timezone' => $this->options['user_timezone']]
        );
    }
}
