<?php
declare(strict_types = 1);
namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Stopwatch\Stopwatch;

class MonitoringSubscriber implements EventSubscriberInterface
{
    const REQUEST_TIMER = 'app.request_time';

    private $logger;
    private $stopwatch;

    public function __construct(LoggerInterface $logger, Stopwatch $stopwatch)
    {
        $this->logger = $logger;
        $this->stopwatch = $stopwatch;
    }

    public static function getSubscribedEvents()
    {
        return [
            // Start timer
            KernelEvents::REQUEST => [['requestStart', 512]],
            // Stop timer and log the duration
            KernelEvents::TERMINATE => [['terminateEnd', 0]],
        ];
    }

    public function requestStart(KernelEvent $event)
    {
        if ($event->isMasterRequest()) {
            $this->stopwatch->start(self::REQUEST_TIMER, 'section');
        }
    }

    public function terminateEnd(KernelEvent $event)
    {
        $this->logRequestTime($event);
    }

    private function logRequestTime(KernelEvent $event)
    {
        if ($event->isMasterRequest()) {
            $this->stopwatch->stop(self::REQUEST_TIMER);
        }

        $controllerAction = $event->getRequest()->attributes->get('_controller', '');

        // Skip if we can't find a controller, or if it isn't a App Controller
        if (!$controllerAction || strpos($controllerAction, 'App\Controller') === false) {
            return;
        }

        // Strip off the common preamble for the sake of readability
        $controllerAction = str_replace('App\\Controller\\', '', $controllerAction);

        // Skip if it is the status controller
        // This gets pinged every 15 seconds by the ELB and we don't need that noise
        if ($controllerAction == 'StatusController::showAction') {
            return;
        }

        $controllerPeriod = $this->getControllerPeriod();
        if ($controllerPeriod) {
            $this->logger->info('CONTROLLER {0} {1}', [$controllerAction, $controllerPeriod]);
        }
    }

    private function getControllerPeriod()
    {
        foreach ($this->stopwatch->getSections() as $section) {
            $event = $section->getEvents()[self::REQUEST_TIMER] ?? null;
            if ($event) {
                return $event->getDuration();
            }
        }

        return null;
    }
}
