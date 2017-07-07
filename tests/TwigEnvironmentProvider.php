<?php
declare(strict_types = 1);
namespace Tests\App;

use App\Ds2013\Helpers\HelperFactory;
use App\Ds2013\PresenterFactory;
use App\Translate\TranslateProvider;
use App\Twig\DesignSystemPresenterExtension;
use App\Twig\GelIconExtension;
use App\Twig\HtmlUtilitiesExtension;
use App\Twig\RdfaSchemaExtension;
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

    /** @var PresenterFactory */
    static private $presenterFactory;

    public static function twig(): Twig_Environment
    {
        if (self::$twig === null) {
            self::build();
        }

        return self::$twig;
    }

    public static function presenterFactory(): PresenterFactory
    {
        if (self::$presenterFactory === null) {
            self::build();
        }

        return self::$presenterFactory;
    }

    private static function build(): void
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

        $translateProvider = new TranslateProvider($translateFactory);

        $assetPackages = new Packages(new Package(new EmptyVersionStrategy()));

        $routeCollectionBuilder = new RouteCollectionBuilder(new YamlFileLoader(
            new FileLocator([__DIR__ . '/../app/config'])
        ));
        $routeCollectionBuilder->import('routing.yml');

        // Symfony extensions

        $twig->addExtension(new AssetExtension($assetPackages));

        $router = new UrlGenerator(
            $routeCollectionBuilder->build(),
            new RequestContext()
        );
        $twig->addExtension(new RoutingExtension($router));

        // Programmes extensions
        $helperFactory = new HelperFactory($translateProvider, $router);

        // Set presenter factory for template tests to use.
        self::$presenterFactory = new PresenterFactory($translateProvider, $router, $helperFactory);

        $twig->addExtension(new DesignSystemPresenterExtension(
            $translateProvider,
            self::$presenterFactory
        ));

        $twig->addExtension(new GelIconExtension());

        $twig->addExtension(new HtmlUtilitiesExtension($assetPackages));

        $twig->addExtension(new RdfaSchemaExtension($assetPackages));

        // Set twig for template tests to use
        self::$twig = $twig;
    }
}
