<?php

namespace App\Ds2013\Helpers;

use RMP\Translate\Translate;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HelperFactory
{
    /** @var Translate */
    private $translate;

    /** @var Router */
    private $router;

    /** @var array */
    private $helpers = [];

    /**
     * This helper factory caches all created objects. DO NOT CREATE HELPERS THAT REQUIRE DATA TO BE CONSTRUCTED.
     * Services are fine, but not per-page data.
     * All functions in this factory should take no parameters. Think of these as a less braindead version
     * of PHP traits.
     */
    public function __construct(Translate $translate, UrlGeneratorInterface $router)
    {
        $this->translate = $translate;
        $this->router = $router;
    }

    public function getLiveBroadcastHelper()
    {
        if (!isset($this->helpers[LiveBroadcastHelper::class])) {
            $this->helpers[LiveBroadcastHelper::class] = new LiveBroadcastHelper();
        }
        return $this->helpers[LiveBroadcastHelper::class];
    }

    public function getPlayTranslationsHelper()
    {
        if (!isset($this->helpers[PlayTranslationsHelper::class])) {
            $this->helpers[PlayTranslationsHelper::class] = new PlayTranslationsHelper($this->translate);
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
