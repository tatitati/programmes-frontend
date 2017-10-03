<?php
declare(strict_types = 1);

namespace App\EventSubscriber;

use App\Fixture\Exception\ScenarioDeletedException;
use App\Fixture\Exception\ScenarioGenerationException;
use App\Fixture\ScenarioManagement\ScenarioManager;
use App\Fixture\ScenarioManagement\ScenarioState;
use App\Fixture\ScenarioManagement\ScenarioReader;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class FixtureSubscriber implements EventSubscriberInterface, ServiceSubscriberInterface
{
    /** @var ContainerInterface */
    private $container;

    public static function getSubscribedServices()
    {
        return [
            ScenarioState::class,
            ScenarioManager::class,
        ];
    }


    public static function getSubscribedEvents()
    {
        return [
            // High priority. We want this to be the first thing that runs
            KernelEvents::REQUEST => [['runAtStartOfRequest', 2048]],
            // Save fixtures on end of request
            KernelEvents::TERMINATE => [['runAtEndOfRequest', 0]],
            KernelEvents::EXCEPTION => [['friendlyGenerationErrors', -64]],
        ];
    }

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function friendlyGenerationErrors(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if ($exception instanceof ScenarioGenerationException
            || $this->container->get(ScenarioState::class)->scenarioGenerationIsActive()
            || $this->container->get(ScenarioState::class)->scenarioDeletionIsActive()
        ) {
            $response = new Response(
                'An error occured. ' . (string) $exception,
                500
            );
            if ($exception instanceof ScenarioDeletedException) {
                $response = new Response(
                    'Scenario deleted',
                    200
                );
                // This overrides the status code in the response
                $response->headers->set('X-Status-Code', 200);
            }
            $event->setResponse($response);
        }
    }

    public function runAtStartOfRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if ($this->container->get(ScenarioState::class)->scenarioIsActive()) {
            $this->container->get(ScenarioManager::class)->runAtStartOfRequest();
        }
    }

    public function runAtEndOfRequest(PostResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (!$this->container->get(ScenarioState::class)->scenarioIsActive()) {
            return;
        }
        $this->container->get(ScenarioManager::class)->runAtEndOfRequest($event->getResponse()->getStatusCode());
    }
}
