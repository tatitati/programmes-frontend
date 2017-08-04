<?php
declare(strict_types = 1);

namespace App\Ds2013\Molecule\DateList;

use App\Exception\InvalidOptionException;
use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\ChronosInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DateListPresenter extends Presenter
{
    /** @inheritDoc */
    protected $options = [
        'css_classes' => '',
        'user_timezone' => 'GMT',
        'format' => 'day',
    ];

    /** @var ChronosInterface */
    private $datetime;

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var Service */
    private $service;

    public function __construct(UrlGeneratorInterface $router, ChronosInterface $datetime, Service $service, array $options = [])
    {
        parent::__construct($options);
        $this->datetime = $datetime;
        $this->router = $router;
        $this->service = $service;
    }

    public function getDateListItem(int $offset): AbstractDateListItemPresenter
    {
        $dateListItemPresenter = __NAMESPACE__ . '\\' . ucfirst($this->getOption('format')) . 'DateListItemPresenter';

        // @codingStandardsIgnoreStart
        return new $dateListItemPresenter(
            $this->router,
            $this->datetime,
            $this->service,
            $offset,
            ['user_timezone' => $this->options['user_timezone']]
        );
        // @codingStandardsIgnoreEnd
    }

    protected function validateOptions(array $options): void
    {
        parent::validateOptions($options);

        if (!in_array($this->getOption('format'), ['day', 'month', 'year'])) {
            throw new InvalidOptionException("Format must be 'day', 'month' or 'year'");
        }
    }
}
