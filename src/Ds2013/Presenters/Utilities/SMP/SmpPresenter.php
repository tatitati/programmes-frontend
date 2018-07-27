<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Utilities\SMP;

use App\Ds2013\Presenter;
use App\DsShared\Helpers\SmpPlaylistHelper;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SmpPresenter extends Presenter
{
    protected $options = [
        'sizes' => [
            0 => 640,
            695 => 976,
        ],
        'uas' => true,
    ];

    /** @var ProgrammeItem */
    private $programmeItem;

    /** @var Version */
    private $streamableVersion;

    /** @var array */
    private $segmentEvents;

    /** @var SmpPlaylistHelper */
    private $smpPlaylistHelper;

    /** @var string */
    private $analyticsCounterName;

    /** @var array */
    private $analyticsLabels;

    /** @var UrlGeneratorInterface */
    private $router;

    public function __construct(
        ProgrammeItem $programmeItem,
        Version $streamableVersion,
        array $segmentEvents,
        string $analyticsCounterName,
        array $analyticsLabels,
        SmpPlaylistHelper $smpPlaylistHelper,
        UrlGeneratorInterface $router,
        array $options = []
    ) {
        parent::__construct($options);
        $this->programmeItem = $programmeItem;
        $this->streamableVersion = $streamableVersion;
        $this->segmentEvents = $segmentEvents;
        $this->smpPlaylistHelper = $smpPlaylistHelper;
        $this->analyticsCounterName = $analyticsCounterName;
        $this->analyticsLabels = $analyticsLabels;
        $this->router = $router;
    }

    public function getProgrammeItem(): ProgrammeItem
    {
        return $this->programmeItem;
    }

    public function getContainerId()
    {
        return 'playout-' . (string) $this->programmeItem->getPid();
    }

    /**
     * @return string[]
     */
    public function getSmpConfig(): array
    {
        $smpPlaylist = $this->smpPlaylistHelper->getSmpPlaylist(
            $this->programmeItem,
            $this->streamableVersion
        );

        return [
            'container' => '#' . $this->getContainerId(),
            'pid' => (string) $this->programmeItem->getPid(),
            'smpSettings' => [
                'autoplay' => ($this->programmeItem instanceof Clip) ? 'true' : 'false',
                'ui' => [
                    'controls' => [
                        'enabled' => true,
                        'always' => $this->programmeItem->getMediaType() ==  MediaTypeEnum::AUDIO ? 'true': 'false',
                    ],
                    'fullscreen' => [
                        'enabled' => $this->programmeItem->getMediaType() ==  MediaTypeEnum::AUDIO ? 'false': 'true',
                    ],
                ],
                'playlistObject' => $smpPlaylist,
                'statsObject' => [
                    'siteId' => $this->analyticsLabels['bbc_site'] ?? '',
                    'product' => $this->analyticsLabels['prod_name'],
                    'appName' => $this->analyticsLabels['app_name'],
                    'appType' => 'responsive',
                    'parentPID'     => (string) $this->programmeItem->getPid(),
                    'parentPIDType' => $this->programmeItem->getType(),
                    'sessionLabels' => [
                        'bbc_site' => $this->analyticsLabels['bbc_site'] ?? '',
                        'event_master_brand' => $this->analyticsLabels['event_master_brand'] ?? '',
                    ],
                ],
                'counterName' => $this->analyticsCounterName,
                'externalEmbedUrl' => $this->router->generate('programme_player', ['pid' => (string) $this->programmeItem->getPid()]),
            ],
            'markers' => $this->smpPlaylistHelper->getMarkers($this->segmentEvents, $this->programmeItem),
        ];
    }

    public function getFactoryOptions(): array
    {
        return [
            'uasConfig' => $this->options['uas'] ? $this->getUasConfig() : null,
        ];
    }

    /**
     * @return string[]
     */
    private function getUasConfig(): array
    {
        return [
            'apiKey' => '', // @todo
            'env' => '', // @todo
            'pid' => (string) $this->programmeItem->getPid(),
            'versionPid' => (string) $this->streamableVersion->getPid(),
            'resourceDomain' => $this->programmeItem->isTv() ? 'tv' : 'radio',
            'resourceType' => $this->programmeItem->getType(),
        ];
    }
}