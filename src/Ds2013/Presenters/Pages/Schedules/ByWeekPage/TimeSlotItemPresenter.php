<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Pages\Schedules\ByWeekPage;

use App\Ds2013\Presenters\Domain\Broadcast\BroadcastPresenter;
use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\SingleServiceBroadcastInfoInterface;
use Cake\Chronos\Chronos;

class TimeSlotItemPresenter extends Presenter
{
    /** @var Chronos */
    private $dateTime;

    /** @var int */
    private $day;

    /** @var Broadcast[][][] */
    private $groupedBroadcasts;

    /** @var int */
    private $hour;

    /**
     * This is sent through to avoid creating a load of Chronos objects when calling isToday()
     * @var Chronos
     */
    private $now;

    public function __construct(int $day, int $hour, Chronos $startOfWeek, Chronos $now, array $groupedBroadcasts, array $options = [])
    {
        parent::__construct($options);
        $this->day = $day;
        $this->hour = $hour;
        $this->dateTime = $startOfWeek->modify("$day day $hour hour");
        $this->groupedBroadcasts = $groupedBroadcasts;
        $this->now = $now;
    }

    public function getDay(): int
    {
        return $this->day;
    }

    public function getDatetime(): Chronos
    {
        return $this->dateTime;
    }

    /**
     * @return Broadcast[]|null
     */
    public function getGroupedBroadcasts(): ?array
    {
        return $this->groupedBroadcasts[$this->dateTime->format('Y/m/d')][$this->hour] ?? null;
    }

    public function getBroadcastItem(SingleServiceBroadcastInfoInterface $broadcast): BroadcastPresenter
    {
        //@TODO should this just come from the twig template/factory?
        return new BroadcastPresenter($broadcast, null, [
            'highlight_box_classes' => 'highlight-box--list',
            'container_classes' => 'broadcast--grid',
            'show_image' => false,
            'show_overlay' => false,
            'show_resume_at' => false,
            'is_stacked' => true,
        ]);
    }

    public function isToday(): bool
    {
        return $this->dateTime->toDateString() === $this->now->toDateString();
    }
}
