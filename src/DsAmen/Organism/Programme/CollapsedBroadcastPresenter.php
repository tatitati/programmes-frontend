<?php
declare(strict_types=1);

namespace App\DsAmen\Organism\Programme;

use App\DsAmen\Organism\Programme\CollapsedBroadcastSubPresenters\CollapsedBroadcastProgrammeCtaPresenter;
use App\DsAmen\Organism\Programme\CollapsedBroadcastSubPresenters\CollapsedBroadcastDetailsPresenter;
use App\DsAmen\Organism\Programme\SubPresenters\ProgrammeCtaPresenter;
use App\DsShared\Helpers\HelperFactory;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CollapsedBroadcastPresenter extends ProgrammePresenter
{
    /** @var CollapsedBroadcast */
    private $collapsedBroadcast;

    /** @var TranslateProvider */
    private $translateProvider;

    public function __construct(
        CollapsedBroadcast $collapsedBroadcast,
        UrlGeneratorInterface $router,
        TranslateProvider $translateProvider,
        HelperFactory $helperFactory,
        array $options
    ) {
        $this->collapsedBroadcast = $collapsedBroadcast;
        $this->translateProvider = $translateProvider;
        parent::__construct($router, $helperFactory, $collapsedBroadcast->getProgrammeItem(), $options);
    }

    public function getCollapsedBroadcastDetailsPresenter(): CollapsedBroadcastDetailsPresenter
    {
        return new CollapsedBroadcastDetailsPresenter(
            $this->collapsedBroadcast,
            $this->translateProvider,
            $this->helperFactory->getLocalisedDaysAndMonthsHelper(),
            $this->helperFactory->getBroadcastNetworksHelper(),
            $this->options
        );
    }

    public function getWatchFromStartCollapsedBroadcastCtaPresenter(): ProgrammeCtaPresenter
    {
        $options = $this->subPresenterOptions('cta_options');
        $options['link_to_start'] = true;
        $options['cta_class'] = '';

        return new CollapsedBroadcastProgrammeCtaPresenter(
            $this->collapsedBroadcast,
            $this->router,
            $this->helperFactory->getLiveBroadcastHelper(),
            $options
        );
    }

    public function showWatchFromStartCta(): bool
    {
        return $this->collapsedBroadcast->isOnAir() && !$this->collapsedBroadcast->getProgrammeItem()->isRadio();
    }

    protected function buildProgrammeCtaPresenter(): ?ProgrammeCtaPresenter
    {
        if ($this->collapsedBroadcast->getProgrammeItem()->isStreamable() || $this->collapsedBroadcast->isOnAir()) {
            return new CollapsedBroadcastProgrammeCtaPresenter(
                $this->collapsedBroadcast,
                $this->router,
                $this->helperFactory->getLiveBroadcastHelper(),
                $this->subPresenterOptions('cta_options')
            );
        }

        return null;
    }
}
