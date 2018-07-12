<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    public function __construct($environment, $debug)
    {
        date_default_timezone_set(@date_default_timezone_get());
        parent::__construct($environment, $debug);

        // For testing if you need to fix to an exact time
        // \BBC\ProgrammesPagesService\Domain\ApplicationTime::setTime(
        //     (int) (new DateTimeImmutable('2016-01-03 03:00:00Z'))->format('U')
        // );
    }

    public function getCacheDir()
    {
        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    public function getLogDir()
    {
        return $this->getProjectDir() . '/var/log';
    }

    public function registerBundles()
    {
        $contents = require $this->getProjectDir() . '/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        // Some environments inherit the configuration of others. For instance
        // `prod_int` should load all the config for the `prod` environment
        // before loading the config for `prod_int`. The key is an environment,
        //and the value is an array of configs that should be loaded prior to
        // the current environment's config.
        // e.g. If the env is `prod_int_fixture` then the following config paths
        // shall be loaded in the order: `prod`, `prod_int`, `prod_int_fixture`
        $derivativeEnvs = [
            'prod_int' => ['prod'],
            'prod_test' => ['prod'],
            'dev_fixture' => ['dev'],
            'prod_int_fixture' => ['prod', 'prod_int'],
            'prod_test_fixture' => ['prod', 'prod_test'],
        ];

        $envsList = [$this->environment];
        if (array_key_exists($this->environment, $derivativeEnvs)) {
            array_unshift($envsList, ...$derivativeEnvs[$this->environment]);
        }

        $container->setParameter('container.autowiring.strict_mode', true);
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getProjectDir() . '/config';

        // Load generated parameters.yaml, as we prefer generating config files
        // rather than environment variables for configuration
        $loader->load($confDir . '/parameters.yaml', 'yaml');

        $loader->load($confDir . '/{packages}/*' . self::CONFIG_EXTS, 'glob');

        foreach ($envsList as $envName) {
            $loader->load($confDir . '/{packages}/' . $envName . '/**/*' . self::CONFIG_EXTS, 'glob');
        }

        // If we are in a fixtured environment then load the fixture config
        if (substr($this->environment, -8) == '_fixture') {
            $loader->load($confDir . '/fixture_db.yaml', 'yaml');
        }

        $loader->load($confDir . '/{services}' . self::CONFIG_EXTS, 'glob');
        foreach ($envsList as $envName) {
            $loader->load($confDir . '/{services}_' . $envName . self::CONFIG_EXTS, 'glob');
        }
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $confDir = $this->getProjectDir() . '/config';
        $routes->import($confDir . '/{routes}' . self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir . '/{routes}/*' . self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir . '/{routes}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, '/', 'glob');
    }
}
