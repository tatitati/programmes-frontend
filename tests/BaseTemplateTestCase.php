<?php
declare(strict_types = 1);
namespace Tests\App;

use App\Ds2013\Presenter;
use Symfony\Component\DomCrawler\Crawler;
use Twig_Environment;
use PHPUnit\Framework\TestCase;

abstract class BaseTemplateTestCase extends TestCase
{
    /** @var Twig_Environment */
    static private $twig;

    public static function setUpBeforeClass()
    {
        if (self::$twig === null) {
            self::$twig = TwigEnvironmentProvider::twig();
        }
    }

    /**
     * Get a Dom Crawler populated with a given Twig template.
     *
     * @param string $name    The template name
     * @param array  $context An array of parameters to pass to the template
     *
     * @return Crawler The Dom Crawler populated with the twig template
     *
     * @throws Twig_Error_Loader  When the template cannot be found
     * @throws Twig_Error_Syntax  When an error occurred during compilation
     * @throws Twig_Error_Runtime When an error occurred during rendering
     */
    protected function crawler(string $name, array $context = []): Crawler
    {
        return new Crawler(
            self::$twig->loadTemplate($name)->render($context)
        );
    }

    protected function presenterCrawler(Presenter $presenter): Crawler
    {
        return $this->crawler($presenter->getTemplatePath(), [
            $presenter->getTemplateVariableName() => $presenter,
        ]);
    }

    protected function assertSchemaOrgItem(string $expected, Crawler $node)
    {
        $this->assertEquals('http://schema.org/', $node->attr('vocab'));
        $this->assertEquals($expected, $node->attr('typeof'));
    }

    protected function assertSchemaOrgPropertyAttr(string $expected, Crawler $node, string $property)
    {
        $this->assertEquals(
            $expected,
            $node->filter('[property="' . $property . '"]')->attr('content')
        );
    }
}
