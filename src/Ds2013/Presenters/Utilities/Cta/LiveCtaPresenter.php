<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Utilities\Cta;

use App\DsShared\Helpers\LiveBroadcastHelper;
use App\DsShared\Helpers\PlayTranslationsHelper;
use App\DsShared\Helpers\StreamableHelper;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LiveCtaPresenter extends CtaPresenter
{
    /** @var CollapsedBroadcast */
    private $collapsedBroadcast;

    /** @var LiveBroadcastHelper */
    private $liveBroadcastHelper;

    /** @var Service|null */
    private $preferredService;

    public function __construct(
        CollapsedBroadcast $collapsedBroadcast,
        ?Service $preferredService,
        PlayTranslationsHelper $playTranslationsHelper,
        UrlGeneratorInterface $router,
        StreamableHelper $streamUrlHelper,
        LiveBroadcastHelper $liveBroadcastHelper,
        array $options
    ) {
        parent::__construct($collapsedBroadcast->getProgrammeItem(), $playTranslationsHelper, $router, $streamUrlHelper, $options);
        $this->collapsedBroadcast = $collapsedBroadcast;
        $this->liveBroadcastHelper = $liveBroadcastHelper;
        $this->preferredService = $preferredService;
    }

    public function getTemplateVariableName(): string
    {
        return 'cta';
    }

    public function getUrl(): string
    {
        return $this->liveBroadcastHelper->simulcastUrl($this->collapsedBroadcast, $this->preferredService);
    }

    public function translatePlayLive(): string
    {
        return $this->playTranslationsHelper->translatePlayLive($this->collapsedBroadcast->getProgrammeItem());
    }
}
