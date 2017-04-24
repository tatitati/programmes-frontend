<?php
declare(strict_types = 1);
namespace App\Twig;

use BBC\GEL\Iconography\IconPathHelper;
use Twig_Extension;
use Twig_Function;

class GelIconExtension extends Twig_Extension
{
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
        if (!isset($this->iconCache[$set][$icon])) {
            if (!isset($this->iconCache[$set])) {
                $this->iconCache[$set] = [];
            }

            $this->iconCache[$set][$icon] = file_get_contents(
                IconPathHelper::getSvgPath($set, $icon)
            );
        }

        return '<i class="gelicon" style="height:' . $height . 'px">' .
            $this->iconCache[$set][$icon] . '</i>';
    }
}
