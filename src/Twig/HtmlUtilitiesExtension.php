<?php
declare(strict_types = 1);
namespace App\Twig;

use Twig_Extension;
use Twig_Function;

class HtmlUtilitiesExtension extends Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_Function('build_css_classes', [$this, 'buildCssClasses']),
        ];
    }

    public function buildCssClasses(array $cssClassTests = []): string
    {
        $cssClasses = [];
        foreach ($cssClassTests as $cssClass => $shouldSet) {
            if ($shouldSet) {
                $cssClasses[] = $cssClass;
            }
        }

        return trim(implode(' ', $cssClasses));
    }
}
