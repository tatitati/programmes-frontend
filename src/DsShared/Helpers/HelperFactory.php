<?php

namespace App\DsShared\Helpers;

use App\Translate\TranslateProvider;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HelperFactory
{
    /** @var TranslateProvider */
    private $translateProvider;

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var array */
    private $helpers = [];

    /**
     * This helper factory caches all created objects. DO NOT CREATE HELPERS THAT REQUIRE DATA TO BE CONSTRUCTED.
     * Services are fine, but not per-page data.
     * All functions in this factory should take no parameters. Think of these as a less braindead version
     * of PHP traits.
     */
    public function __construct(TranslateProvider $translateProvider, UrlGeneratorInterface $router)
    {
        $this->translateProvider = $translateProvider;
        $this->router = $router;
    }

    public function getBroadcastNetworksHelper()
    {
        if (!isset($this->helpers[BroadcastNetworksHelper::class])) {
            $this->helpers[BroadcastNetworksHelper::class] = new BroadcastNetworksHelper($this->translateProvider);
        }
        return $this->helpers[BroadcastNetworksHelper::class];
    }

    public function getLiveBroadcastHelper(): LiveBroadcastHelper
    {
        if (!isset($this->helpers[LiveBroadcastHelper::class])) {
            $this->helpers[LiveBroadcastHelper::class] = new LiveBroadcastHelper($this->router);
        }
        return $this->helpers[LiveBroadcastHelper::class];
    }

    public function getLocalisedDaysAndMonthsHelper(): LocalisedDaysAndMonthsHelper
    {
        if (!isset($this->helpers[LocalisedDaysAndMonthsHelper::class])) {
            $this->helpers[LocalisedDaysAndMonthsHelper::class] = new LocalisedDaysAndMonthsHelper($this->translateProvider);
        }
        return $this->helpers[LocalisedDaysAndMonthsHelper::class];
    }

    public function getPlayTranslationsHelper(): PlayTranslationsHelper
    {
        if (!isset($this->helpers[PlayTranslationsHelper::class])) {
            $this->helpers[PlayTranslationsHelper::class] = new PlayTranslationsHelper($this->translateProvider);
        }
        return $this->helpers[PlayTranslationsHelper::class];
    }

    public function getTitleLogicHelper(): TitleLogicHelper
    {
        if (!isset($this->helpers[TitleLogicHelper::class])) {
            $this->helpers[TitleLogicHelper::class] = new TitleLogicHelper();
        }
        return $this->helpers[TitleLogicHelper::class];
    }
}
