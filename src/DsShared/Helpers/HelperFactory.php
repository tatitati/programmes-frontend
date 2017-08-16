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

    public function getLiveBroadcastHelper()
    {
        if (!isset($this->helpers[LiveBroadcastHelper::class])) {
            $this->helpers[LiveBroadcastHelper::class] = new LiveBroadcastHelper($this->router);
        }
        return $this->helpers[LiveBroadcastHelper::class];
    }

    public function getPlayTranslationsHelper()
    {
        if (!isset($this->helpers[PlayTranslationsHelper::class])) {
            $this->helpers[PlayTranslationsHelper::class] = new PlayTranslationsHelper($this->translateProvider);
        }
        return $this->helpers[PlayTranslationsHelper::class];
    }

    public function getTitleLogicHelper()
    {
        if (!isset($this->helpers[TitleLogicHelper::class])) {
            $this->helpers[TitleLogicHelper::class] = new TitleLogicHelper();
        }
        return $this->helpers[TitleLogicHelper::class];
    }
}
