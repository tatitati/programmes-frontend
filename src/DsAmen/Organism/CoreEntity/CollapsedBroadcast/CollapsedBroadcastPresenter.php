<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\CoreEntity\CollapsedBroadcast;

use App\DsAmen\Organism\CoreEntity\Base\BaseCoreEntityPresenter;
use App\DsAmen\Organism\CoreEntity\Base\SubPresenter\BaseBodyPresenter;
use App\DsAmen\Organism\CoreEntity\Base\SubPresenter\BaseImagePresenter;
use App\DsAmen\Organism\CoreEntity\Base\SubPresenter\BaseTitlePresenter;
use App\DsAmen\Organism\CoreEntity\Base\SubPresenter\BaseCtaPresenter;
use App\DsAmen\Organism\CoreEntity\CollapsedBroadcast\SubPresenter\CtaPresenter;
use App\DsAmen\Organism\CoreEntity\CollapsedBroadcast\SubPresenter\DetailsPresenter;
use App\DsAmen\Organism\CoreEntity\Shared\SubPresenter\SharedBodyPresenter;
use App\DsAmen\Organism\CoreEntity\Shared\SubPresenter\SharedImagePresenter;
use App\DsAmen\Organism\CoreEntity\Shared\SubPresenter\SharedTitlePresenter;
use App\DsShared\Helpers\HelperFactory;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CollapsedBroadcastPresenter extends BaseCoreEntityPresenter
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
        array $options = []
    ) {
        parent::__construct($collapsedBroadcast->getProgrammeItem(), $router, $helperFactory, $options);

        $this->collapsedBroadcast = $collapsedBroadcast;
        $this->translateProvider = $translateProvider;
    }

    public function getBodyPresenter(array $options = []): BaseBodyPresenter
    {
        $options = array_merge($this->subPresenterOptions('body_options'), $options);
        return new SharedBodyPresenter(
            $this->coreEntity,
            $options
        );
    }

    public function getCtaPresenter(array $options = []): ?BaseCtaPresenter
    {
        $options = array_merge($this->subPresenterOptions('cta_options'), $options);
        return new CtaPresenter(
            $this->collapsedBroadcast,
            $this->router,
            $this->helperFactory->getLiveBroadcastHelper(),
            $options
        );
    }

    public function getDetailsPresenter(): DetailsPresenter
    {
        return new DetailsPresenter(
            $this->collapsedBroadcast,
            $this->translateProvider,
            $this->helperFactory->getLocalisedDaysAndMonthsHelper(),
            $this->helperFactory->getBroadcastNetworksHelper(),
            $this->options
        );
    }

    public function getImagePresenter(array $options = []): BaseImagePresenter
    {
        $options = array_merge($this->subPresenterOptions('image_options'), $options);
        $options['cta_options'] = $this->mergeWithSubPresenterOptions($options, 'cta_options');
        return new SharedImagePresenter(
            $this->coreEntity,
            $this->router,
            $this->getCtaPresenter(),
            $options
        );
    }

    public function getTitlePresenter(array $options = []): BaseTitlePresenter
    {
        $options = array_merge($this->subPresenterOptions('title_options'), $options);
        return new SharedTitlePresenter(
            $this->coreEntity,
            $this->router,
            $this->helperFactory->getTitleLogicHelper(),
            $options
        );
    }

    public function showStandaloneCta(): bool
    {
        return !$this->getOption('show_image') &&
            $this->coreEntity instanceof ProgrammeItem &&
            ($this->coreEntity->isStreamable() || $this->collapsedBroadcast->isOnAir());
    }

    public function showWatchFromStartCta(): bool
    {
        return $this->collapsedBroadcast->isOnAir() &&
            !($this->coreEntity instanceof ProgrammeItem && $this->coreEntity->isRadio());
    }
}
