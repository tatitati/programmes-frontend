<?php
declare(strict_types = 1);
namespace Tests\App\Twig;

use App\Twig\HtmlUtilitiesExtension;
use PHPUnit\Framework\TestCase;

class HtmlUtilitiesExtensionTest extends TestCase
{
    public function testBuildCssClasses()
    {
        $extension = new HtmlUtilitiesExtension();
        $this->assertSame('foo baz qux', $extension->buildCssClasses([
            'foo' => true,
            'bar' => false,
            'baz qux' => true,
        ]));
    }
}
