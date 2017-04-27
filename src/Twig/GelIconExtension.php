<?php
declare(strict_types = 1);
namespace App\Twig;

use BBC\GEL\Iconography\IconPathHelper;
use Twig_Extension;
use Twig_Function;

class GelIconExtension extends Twig_Extension
{
    private const SVG_DIMENSIONS = '!(?<=^\<svg xmlns="http://www.w3.org/2000/svg" )width="(\d+)" height="(\d+)"!';

    private $iconCache = [];

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_Function('gelicon', [$this, 'gelicon'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function gelicon($set, $icon, $height)
    {
        if (!isset($this->iconCache["${set}|${icon}|${height}"])) {
            // Icons contain explicit width and height attributes.
            // We want to add a class attribute, remove the width and replace
            // the height with our own. Removing the width is fine as the svg
            // shall still have its viewbox attribute which give the svg an
            // intrinsic ratio.
            // <svg xmlns="..." width="32px" height="32px" viewbox="..."> becomes
            // <svg xmlns="..." class="gelicon" height="16" viewbox="...">
            $this->iconCache["${set}|${icon}|${height}"] = preg_replace(
                self::SVG_DIMENSIONS,
                'class="gelicon" height="' . $height . '"',
                file_get_contents(IconPathHelper::getSvgPath($set, $icon))
            );
        }

        return $this->iconCache["${set}|${icon}|${height}"];
    }
}
