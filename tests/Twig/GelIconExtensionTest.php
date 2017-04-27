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
        $expectedPattern = '$<svg xmlns="http://www.w3.org/2000/svg" class="gelicon" height="16" viewBox="[0-9 ]+">.+</svg>$';
        $this->assertRegexp($expectedPattern, $extension->gelicon('core', 'search', 16));
    }
}
