<?php
declare(strict_types=1);

namespace App\Ds2013\Presenters\Domain\BroadcastEvent;

use App\Ds2013\Presenter;
use App\DsShared\Helpers\BroadcastNetworksHelper;
use App\DsShared\Helpers\LiveBroadcastHelper;
use App\DsShared\Helpers\LocalisedDaysAndMonthsHelper;
use App\Exception\InvalidOptionException;
use App\ValueObject\BroadcastNetworkBreakdown;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BroadcastEventPresenter extends Presenter
{
    /** @var CollapsedBroadcast */
    private $collapsedBroadcast;

    /** @var BroadcastNetworkBreakdown[] */
    private $networkBreakdown;

    /** @var LocalisedDaysAndMonthsHelper */
    private $daysAndMonthsHelper;

    /** @var LiveBroadcastHelper */
    private $liveBroadcastHelper;

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var array */
    protected $options = [
        'container_classes' => '',
        'image_classes' => '',
        'show_logo' => true,
        'show_watch_from_start' => false,
        'show_on_air_message' => true,
    ];

    public function __construct(
        CollapsedBroadcast $collapsedBroadcast,
        BroadcastNetworksHelper $broadcastNetworksHelper,
        LocalisedDaysAndMonthsHelper $daysAndMonthsHelper,
        LiveBroadcastHelper $liveBroadcastHelper,
        UrlGeneratorInterface $router,
        array $options = []
    ) {
        parent::__construct($options);
        $this->collapsedBroadcast = $collapsedBroadcast;
        $this->networkBreakdown = $broadcastNetworksHelper->getNetworksAndServicesDetails($collapsedBroadcast);
        $this->daysAndMonthsHelper = $daysAndMonthsHelper;
        $this->router = $router;
        $this->liveBroadcastHelper = $liveBroadcastHelper;
    }

    /** @return BroadcastNetworkBreakdown[] */
    public function getNetworkBreakdown(): array
    {
        return $this->networkBreakdown;
    }

    public function getMainBroadcastNetwork() :?Network
    {
        $breakdowns = $this->getNetworkBreakdown();

        foreach ($breakdowns as $breakdown) {
            if ($breakdown->getNetwork()) {
                return $breakdown->getNetwork();
            }
        }

        return null;
    }

    public function getCollapsedBroadcast(): CollapsedBroadcast
    {
        return $this->collapsedBroadcast;
    }

    public function getBroadcastDay(): string
    {
        return $this->daysAndMonthsHelper->getFormatedDay($this->collapsedBroadcast->getStartAt());
    }

    public function getNetworkUrl(?Network $network): string
    {
        if ($network) {
            return $this->router->generate(
                'network',
                ['networkUrlKey' => $network->getUrlKey()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        return '';
    }

    public function isOnAir(): bool
    {
        return $this->collapsedBroadcast->isOnAir();
    }

    public function getOnAirMessage(): string
    {
        return $this->collapsedBroadcast->getProgrammeItem()->isRadio() ? 'on_air' : 'on_now';
    }

    public function getRewindUrl(): ?string
    {
        if ($this->collapsedBroadcast->getProgrammeItem()->isTv() && $this->liveBroadcastHelper->isWatchableLive($this->collapsedBroadcast)) {
            return $this->liveBroadcastHelper->simulcastUrl($this->collapsedBroadcast, null, ['rewindTo' => 'current']);
        }

        return null;
    }

    protected function validateOptions(array $options): void
    {
        parent::validateOptions($options);

        if (!is_bool($this->getOption('show_logo'))) {
            throw new InvalidOptionException("show_logo must a bool");
        }

        if (!is_string($this->getOption('container_classes'))) {
            throw new InvalidOptionException("container_classes must a string");
        }

        if (!is_string($this->getOption('image_classes'))) {
            throw new InvalidOptionException("image_classes must a string");
        }

        if (!is_bool($this->getOption('show_watch_from_start'))) {
            throw new InvalidOptionException("show_watch_from_start must a boolean");
        }

        if (!is_bool($this->getOption('show_on_air_message'))) {
            throw new InvalidOptionException("show_on_air_message must a boolean");
        }
    }
}
