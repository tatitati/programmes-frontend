<?php
declare(strict_types = 1);
namespace Tests\App;

use App\Ds2013\PresenterFactory;
use App\Twig\DesignSystemPresenterExtension;
use App\Twig\GelIconExtension;
use App\Twig\HtmlUtilitiesExtension;
use RMP\Translate\TranslateFactory;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Symfony\Component\Routing\RequestContext;
use Twig_Environment;
use Twig_Loader_Filesystem;

class TwigEnvironmentProvider
{
    /** @var Twig_Environment */
    static private $twig;

    public static function twig(): Twig_Environment
    {
        if (self::$twig === null) {
            self::$twig = self::buildTwig();
        }

        return self::$twig;
    }

    private static function buildTwig(): Twig_Environment
    {
        $loader = new Twig_Loader_Filesystem(__DIR__ . '/../app/Resources');
        $loader->addPath(__DIR__ . '/../src/Ds2013', 'Ds2013');

        $twig = new Twig_Environment($loader, ['strict_variables' => true]);

        $translateFactory = new TranslateFactory([
            'fallback_locale' => 'en_GB',
            'cachepath' => __DIR__ . '/../tmp/cache/test/translations',
            'domains' => ['programmes'],
            'default_domain' => 'programmes',
            'debug' => true,
            'basepath' => __DIR__ . '/../app/Resources/translations',
        ]);

        $translate = $translateFactory->create('en_GB');

        $assetPackages = new Packages(new Package(new EmptyVersionStrategy()));

        $routeCollectionBuilder = new RouteCollectionBuilder(new YamlFileLoader(
            new FileLocator([__DIR__ . '/../app/config'])
        ));
        $routeCollectionBuilder->import('routing.yml');

        // Symfony extensions

        $twig->addExtension(new AssetExtension($assetPackages));

        $twig->addExtension(new RoutingExtension(new UrlGenerator(
            $routeCollectionBuilder->build(),
            new RequestContext()
        )));

        // Programmes extensions

        $twig->addExtension(new DesignSystemPresenterExtension(
            $translate,
            new PresenterFactory($translate)
        ));

        $twig->addExtension(new GelIconExtension());

        $twig->addExtension(new HtmlUtilitiesExtension($assetPackages));

        return $twig;
    }
}
