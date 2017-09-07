<?php
declare(strict_types = 1);

namespace App\EventSubscriber;

use App\Fixture\ScenarioManagement\ScenarioNameManager;
use BBC\ProgrammesPagesService\Cache\Cache;
use BBC\ProgrammesPagesService\Cache\CacheInterface;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use RuntimeException;

class FixtureSubscriber implements EventSubscriberInterface
{
    /** @var ContainerInterface */
    private $container;

    public static function getSubscribedEvents()
    {
        return [
            // High priority. We want this to be the first thing that runs
            KernelEvents::REQUEST => [['setupFixtureCode', 2048]],
        ];
    }

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setupFixtureCode(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $scenarioNameManager = $this->container->get(ScenarioNameManager::class);
        if ($scenarioNameManager->scenarioIsActive()) {
            // We are in fixture mode ladies and gentlemen. We will now perform dark magic.
            // Note that switching to the fixture DB is handled in App\Fixture\Doctrine\ConnectionFactory
            $this->disableCachingAcrossApp();
        }
    }

    private function disableCachingAcrossApp()
    {
        $servicesToOverride = ['cache.programmes', 'cache.app_page_chrome', Cache::class, CacheInterface::class];
        foreach ($servicesToOverride as $serviceName) {
            if ($this->container->initialized($serviceName)) {
                // We should not override services that have been initialised
                // they will already have been injected, and this behaviour will
                // be removed in Symfony 4. See https://github.com/symfony/symfony/pull/19668
                // If this blows up, check what service are being passed in via the constructor
                // to all of the other event subscribers, odds are one of those services will be requiring
                // one of the ones we want to override. Try passing in the container instead.
                throw new RuntimeException("Service $serviceName is already initialised. Cannot override.");
            }
        }
        $nullAdapter = $this->container->get('cache.null');
        $this->container->set('cache.programmes', $nullAdapter);
        $this->container->set('cache.app_page_chrome', $nullAdapter);
        $nullProgrammesCache = new Cache($nullAdapter, 'null');
        $nullProgrammesCache->setFlushCacheItems(true);
        $this->container->set(Cache::class, $nullProgrammesCache);
        $this->container->set(CacheInterface::class, $nullProgrammesCache);
    }
}
