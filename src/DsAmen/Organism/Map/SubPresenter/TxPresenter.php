<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Map\SubPresenter;

use App\DsShared\Helpers\LiveBroadcastHelper;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Tx means Transmission
 */
class TxPresenter extends RightColumnPresenter
{
    /** @var CollapsedBroadcast|null */
    private $upcomingBroadcast;

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

    /** @var int */
    private $debutsCount;

    /** @var int */
    private $repeatsCount;

    public function __construct(
        LiveBroadcastHelper $liveBroadcastHelper,
        TranslateProvider $translateProvider,
        UrlGeneratorInterface $router,
        ProgrammeContainer $programmeContainer,
        ?CollapsedBroadcast $upcomingBroadcast,
        int $debutsCount,
        int $repeatsCount,
        array $options = []
    ) {

        parent::__construct($programmeContainer, $options);

        $this->liveBroadcastHelper = $liveBroadcastHelper;
        $this->translateProvider = $translateProvider;
        $this->router = $router;
        $this->upcomingBroadcast = $upcomingBroadcast;
        $this->debutsCount = $debutsCount;
        $this->repeatsCount = $repeatsCount;
    }

    public function getBadgeTranslationString(): string
    {
        // Radio brand pages, repeats and programmes direct TLEO children don't get badges
        if ($this->programmeContainer->isRadio() ||
            $this->upcomingBroadcast->isRepeat() ||
            $this->upcomingBroadcast->getProgrammeItem()->getParent()->isTleo()
        ) {
            return '';
        }

        return $this->upcomingBroadcast->getProgrammeItem()->getPosition() === 1 ? 'new_series' : 'new';
    }

    public function getUpcomingBroadcast(): ?CollapsedBroadcast
    {
        return $this->upcomingBroadcast;
    }

    public function getLinkTitleTranslationString(): string
    {
        return $this->upcomingBroadcast ? 'see_all_upcoming_of' : 'see_all_episodes_from';
    }

    public function getLinkTextTranslationString(): string
    {
        return $this->upcomingBroadcast ? 'upcoming_episodes' : 'all_previous_episodes';
    }

    public function getProgrammeTitle(): string
    {
        return $this->programmeContainer->getTitle();
    }

    public function getTitleTranslationString(): string
    {
        $isWatchableLive = $this->upcomingBroadcast && $this->liveBroadcastHelper->isWatchableLive($this->upcomingBroadcast);

        if ($this->programmeContainer->isRadio()) {
            return $isWatchableLive ? 'on_air' : 'coming_up';
        }

        if ($isWatchableLive) {
            return 'on_now';
        }

        if ($this->programmeContainer->getNetwork()->isInternational() || $this->upcomingBroadcast) {
            return 'next_on';
        }

        return 'on_tv';
    }

    public function getTrailingLinkHref(): string
    {
        if ($this->upcomingBroadcast) {
            return $this->router->generate(
                'programme_upcoming_broadcasts',
                ['pid' => $this->programmeContainer->getPid()]
            );
        }

        return $this->router->generate('programme_episodes', ['pid' => $this->programmeContainer->getPid()]);
    }

    public function getUpcomingBroadcastCount(): string
    {
        // Only radio pages split between repeats and debuts
        if ($this->programmeContainer->isRadio()) {
            if ($this->repeatsCount > 0) {
                return $this->translateProvider->getTranslate()->translate(
                    'x_new_and_repeats',
                    ['%1' => $this->debutsCount, '%count%' => $this->repeatsCount],
                    $this->repeatsCount
                );
            }

            return $this->translateProvider->getTranslate()->translate(
                'x_new',
                ['%count%' => $this->debutsCount],
                $this->debutsCount
            );
        }

        // All other pages show episodes count
        return $this->translateProvider->getTranslate()->translate(
            'x_total',
            ['%1' => $this->debutsCount + $this->repeatsCount],
            $this->debutsCount + $this->repeatsCount
        );
    }

    public function showImage(): bool
    {
        // Only show image if it's not a minimap and the programme item image is different from the context programme image
        return !$this->getOption('show_mini_map') &&
            (string) $this->upcomingBroadcast->getProgrammeItem()->getImage()->getPid() !==
            (string) $this->programmeContainer->getImage()->getPid();
    }

    public function showUpcomingBroadcastCount(): bool
    {
        return $this->upcomingRepeats || $this->upcomingDebuts;
    }
}
