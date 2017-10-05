<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Map\SubPresenter;

use App\DsAmen\Presenter;
use App\DsShared\Helpers\LiveBroadcastHelper;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Tx means Transmission
 */
class TxPresenter extends Presenter
{
    /** @var CollapsedBroadcast|null */
    private $collapsedBroadcast;

    /** @var ProgrammeContainer */
    private $contextProgramme;

    /** @var LiveBroadcastHelper */
    private $liveBroadcastHelper;

    /** @var TranslateProvider */
    private $translateProvider;

    /** @var CollapsedBroadcast[] */
    private $upcomingDebuts;

    /** @var CollapsedBroadcast[] */
    private $upcomingRepeats;

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var array */
    protected $options = [
        'show_mini_map' => false,
    ];

    public function __construct(
        LiveBroadcastHelper $liveBroadcastHelper,
        TranslateProvider $translateProvider,
        UrlGeneratorInterface $router,
        ProgrammeContainer $contextProgramme,
        array $upcomingBroadcasts,
        array $options = []
    ) {

        parent::__construct($options);

        $this->liveBroadcastHelper = $liveBroadcastHelper;
        $this->translateProvider = $translateProvider;
        $this->router = $router;

        $this->upcomingRepeats = [];
        $this->upcomingDebuts = [];

        foreach ($upcomingBroadcasts as $broadcast) {
            if ($broadcast->isRepeat()) {
                $this->upcomingRepeats[] = $broadcast;
            } else {
                $this->upcomingDebuts[] = $broadcast;
            }
        }

        // Prefer debuts over repeats
        if ($this->upcomingDebuts) {
            $this->collapsedBroadcast = reset($this->upcomingDebuts);
        } elseif ($this->upcomingRepeats) {
            $this->collapsedBroadcast = reset($this->upcomingRepeats);
        }

        $this->contextProgramme = $contextProgramme;
    }

    public function getBadgeTranslationString(): string
    {
        // Radio brand pages and repeats don't have badges
        if ($this->contextProgramme->isRadio() || $this->collapsedBroadcast->isRepeat()) {
            return '';
        }

        return $this->collapsedBroadcast->getProgrammeItem()->getPosition() === 1 ? 'new_series' : 'new';
    }

    public function getCollapsedBroadcast(): ?CollapsedBroadcast
    {
        return $this->collapsedBroadcast;
    }

    public function getContextProgramme(): ProgrammeContainer
    {
        return $this->contextProgramme;
    }

    public function getContextProgrammePid(): string
    {
        return (string) $this->contextProgramme->getPid();
    }

    public function getLinkTitleTranslationString(): string
    {
        return $this->collapsedBroadcast ? 'see_all_upcoming_of' : 'see_all_episodes_from';
    }

    public function getLinkTextTranslationString(): string
    {
        return $this->collapsedBroadcast ? 'upcoming_episodes' : 'all_previous_episodes';
    }

    public function getProgrammeTitle(): string
    {
        return $this->contextProgramme->getTitle();
    }

    public function getTitleTranslationString(): string
    {
        $isWatchableLive = $this->collapsedBroadcast && $this->liveBroadcastHelper->isWatchableLive($this->collapsedBroadcast);

        if ($this->contextProgramme->isRadio()) {
            return $isWatchableLive ? 'on_air' : 'coming_up';
        }

        if ($isWatchableLive) {
            return 'on_now';
        }

        if ($this->contextProgramme->getNetwork()->isInternational() || $this->collapsedBroadcast) {
            return 'next_on';
        }

        return 'on_tv';
    }

    public function getTrailingLinkHref(): string
    {
        if ($this->collapsedBroadcast) {
            return $this->router->generate('programme_broadcasts', ['pid' => $this->contextProgramme->getPid()]);
        }

        return $this->router->generate('programme_episodes', ['pid' => $this->contextProgramme->getPid()]);
    }

    public function getUpcomingBroadcastCount(): string
    {
        $debutsCount = count($this->upcomingDebuts);
        $repeatsCount = count($this->upcomingRepeats);

        // Only radio pages split between repeats and debuts
        if ($this->contextProgramme->isRadio()) {
            if ($repeatsCount > 0) {
                return $this->translateProvider->getTranslate()->translate(
                    'x_new_and_repeats',
                    ['%1' => $debutsCount, '%count%' => $repeatsCount],
                    $repeatsCount
                );
            }

            return $this->translateProvider->getTranslate()->translate(
                'x_new',
                ['%count%' => $debutsCount],
                $debutsCount
            );
        }

        // All other pages show episodes count
        return $this->translateProvider->getTranslate()->translate(
            'x_total',
            ['%1' => $debutsCount + $repeatsCount],
            $debutsCount + $repeatsCount
        );
    }

    public function showImage(): bool
    {
        // Only show image if it's not a minimap and the programme item image is different from the context programme image
        return !$this->getOption('show_mini_map') &&
            (string) $this->collapsedBroadcast->getProgrammeItem()->getImage()->getPid() !==
            (string) $this->contextProgramme->getImage()->getPid();
    }

    public function showUpcomingBroadcastCount(): bool
    {
        return $this->upcomingRepeats || $this->upcomingDebuts;
    }
}
