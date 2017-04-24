<?php
declare(strict_types = 1);
namespace Tests\App\Twig;

use App\Twig\GelIconExtension;
use PHPUnit\Framework\TestCase;

class GelIconExtensionTest extends TestCase
{
    public function testGelicon()
    {
        $extension = new GelIconExtension();
        $expectedPattern = '$<i class="gelicon" style="height:16px"><svg.+>.+</svg></i>$';
        $this->assertRegexp($expectedPattern, $extension->gelicon('core', 'search', 16));
    }
}
