<?php
declare(strict_types = 1);
namespace App\Branding;

use App\Translate\TranslateProvider;
use BBC\BrandingClient\Branding;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Consider "I'm Sorry I Haven't a Clue" - it is a Radio 4 programme. Radio
 * programmes tend not to have bespoke per-brand theming, instead they inherit
 * their theme from their Service (in this case Radio 4). This however poses
 * a small problem - if we display the Service theme, how shall we show the
 * correct per-programme Title and Navigation?
 *
 * This is solved by the Branding Tool outputting templates that contain
 * placeholder sections such as  <!--BRANDING_PLACEHOLDER_TITLE--> and
 * <!--BRANDING_PLACEHOLDER_NAV--> when there is not programme set.
 *
 * This takes a Branding instance that contains those placeholders and a
 * "context" and replaces those placeholders with a title and default
 * navigation, based up the contents of the context.
 */
class BrandingPlaceholderResolver
{
    private const PLACEHOLDER_TITLE = '<!--BRANDING_PLACEHOLDER_TITLE-->';
    private const PLACEHOLDER_NAV = '<!--BRANDING_PLACEHOLDER_NAV-->';
    private const PLACEHOLDER_SPONSOR = '<!--BRANDING_PLACEHOLDER_SPONSOR-->';

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var TranslateProvider */
    private $translateProvider;

    public function __construct(
        UrlGeneratorInterface $router,
        TranslateProvider $translateProvider
    ) {
        $this->router = $router;
        $this->translateProvider = $translateProvider;
    }

    public function resolve(Branding $branding, $context): Branding
    {
        // If the context is not a Programme or Group then don't attempt to resolve
        if (!($context instanceof CoreEntity)) {
            return $branding;
        }

        // Currently placeholders only exist in the bodyFirst section
        return new Branding(
            $branding->getHead(),
            $this->resolvePlaceholders($branding, $context, $branding->getBodyFirst()),
            $branding->getBodyLast(),
            $branding->getColours(),
            $branding->getOptions()
        );
    }

    private function resolvePlaceholders(Branding $branding, $context, string $haystack)
    {
        // Check if the placeholder is present in the haystack, before
        // attempting the replace to avoid doing extra work building the
        // replacement text, if there is nothing to replace.

        // Title
        if (strpos($haystack, self::PLACEHOLDER_TITLE) !== 0) {
            $haystack = str_replace(
                self::PLACEHOLDER_TITLE,
                $this->buildTitle($context),
                $haystack
            );
        }

        // Navigation
        if (strpos($haystack, self::PLACEHOLDER_NAV) !== 0) {
            $haystack = str_replace(
                self::PLACEHOLDER_NAV,
                $this->buildNav($context, $branding),
                $haystack
            );
        }

        // Sponsor
        if (strpos($haystack, self::PLACEHOLDER_SPONSOR) !== 0) {
            $haystack = str_replace(
                self::PLACEHOLDER_SPONSOR,
                $this->buildSponsor($context),
                $haystack
            );
        }

        return $haystack;
    }

    private function buildTitle($context): string
    {
        return sprintf(
            '<a href="%s">%s</a>',
            $this->router->generate('find_by_pid', ['pid' => $context->getTleo()->getPid()]),
            $context->getTleo()->getTitle()
        );
    }

    private function buildNav($context, Branding $branding): string
    {
        $translate = $this->translateProvider->getTranslate();

        // We've already asserted that $context is a Programme or a Group
        $tleo = $context->getTleo();
        $navItems = [];

        $hasEpisodes = false;
        $hasClips = false;
        $hasGalleries = false;

        // Home link is always present
        $navItems[] = $branding->buildNavItem(
            $translate->translate('home'),
            $this->router->generate('find_by_pid', ['pid' => $tleo->getPid()]),
            'nav_home'
        );

        if ($tleo instanceof ProgrammeContainer) {
            $hasEpisodes = $tleo->getAggregatedEpisodesCount() > 0;
        }

        if ($tleo instanceof ProgrammeContainer || $tleo instanceof Episode) {
            $hasClips = $tleo->getAvailableClipsCount() > 0;
            $hasGalleries = $tleo->getAvailableGalleriesCount() > 0;
        }

        // Episodes link
        if ($tleo && $hasEpisodes) {
            $navItems[] = $branding->buildNavItem(
                $translate->translate('episodes'),
                $this->router->generate('programme_episodes', ['pid' => $tleo->getPid()]),
                'nav_episodes'
            );
        }

        // Clips link
        if ($tleo && $hasClips) {
            $navItems[] = $branding->buildNavItem(
                $translate->translate('clips'),
                $this->router->generate('programme_clips', ['pid' => $tleo->getPid()]),
                'nav_clips'
            );
        }

        // Galleries link
        if ($tleo && $hasGalleries) {
            $navItems[] = $branding->buildNavItem(
                $translate->translate('galleries'),
                $this->router->generate('programme_galleries', ['pid' => $tleo->getPid()]),
                'nav_galleries'
            );
        }

        return implode('', $navItems);
    }

    private function buildSponsor($context): string
    {
        // TODO
        return '';
    }
}
