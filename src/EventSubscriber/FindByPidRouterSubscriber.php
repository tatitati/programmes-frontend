<?php
declare(strict_types = 1);
namespace App\EventSubscriber;

use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Collection;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Franchise;
use BBC\ProgrammesPagesService\Domain\Entity\Gallery;
use BBC\ProgrammesPagesService\Domain\Entity\Group;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\Season;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\ServiceFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * A single route /programmes/:pid can serve one of several page types depending
 * upon the type of entity it finds. This entity can be either a Programme,
 * Group, Version or Segment. It is possible to store all of these controller
 * actions within a single class, but then that class becomes gargantuan and
 * thus finding things within it becomes a pain. It would be preferable to split
 * these controllers up into multiple classes - one for the brand page, one for
 * the episode page, one for the clip page etc.
 *
 * This is an event that spots requests to the find_by_pid route, makes a DB
 * query to work out what Controller should be used for a given PID and sets
 * the _controller property to the relevant controller name. We also store the
 * entity in the request attributes so that the controller can access it
 * directly instead of having to make another DB request for the entity.
 *
 * This happens after Routing has taken place (the actions in RouterSubscriber)
 * so we have a request with a _controller value, but before the Controller
 * Resolver is triggered, which takes that _controller value and creates an
 * instance of the controller.
 */
class FindByPidRouterSubscriber implements EventSubscriberInterface
{
    /** @var ServiceFactory */
    private $serviceFactory;

    public static function getSubscribedEvents()
    {
        return [
            // This event needs to run after the RouterSubscriber (which has a
            // priority of 32), so that the initial value of _controller is set
            KernelEvents::REQUEST => [['updateController', 0]],
        ];
    }

    public function __construct(ServiceFactory $serviceFactory)
    {
        $this->serviceFactory = $serviceFactory;
    }

    public function updateController(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Do nothing if this is not a find_by_pid route
        if ($request->attributes->get('_controller') !== '!find_by_pid') {
            return;
        }

        $pid = new Pid($request->attributes->get('pid'));

        // Attempt to find a Programme or Group
        $coreEntity = $this->serviceFactory->getCoreEntitiesService()->findByPidFull($pid);
        if ($coreEntity) {
            if ($coreEntity instanceof ProgrammeContainer) {
                if (!$coreEntity->getParent()) {
                    $request->attributes->set('programme', $coreEntity);
                    $request->attributes->set('_controller', \App\Controller\FindByPid\TlecController::class);
                    return;
                }

                $request->attributes->set('programme', $coreEntity);
                $request->attributes->set('_controller', \App\Controller\FindByPid\SeriesController::class);
                return;
            }

            if ($coreEntity instanceof Episode) {
                $request->attributes->set('episode', $coreEntity);
                $request->attributes->set('_controller', \App\Controller\FindByPid\EpisodeController::class);
                return;
            }

            if ($coreEntity instanceof Clip) {
                $request->attributes->set('clip', $coreEntity);
                $request->attributes->set('_controller', \App\Controller\FindByPid\ClipController::class);
                return;
            }

            if ($coreEntity instanceof Collection) {
                $request->attributes->set('collection', $coreEntity);
                $request->attributes->set('_controller', \App\Controller\FindByPid\CollectionController::class);
                return;
            }

            if ($coreEntity instanceof Gallery) {
                $request->attributes->set('gallery', $coreEntity);
                $request->attributes->set('_controller', \App\Controller\FindByPid\GalleryController::class);
                return;
            }

            if ($coreEntity instanceof Season) {
                $request->attributes->set('season', $coreEntity);
                $request->attributes->set('_controller', \App\Controller\FindByPid\SeasonController::class);
                return;
            }

            if ($coreEntity instanceof Franchise) {
                $request->attributes->set('franchise', $coreEntity);
                $request->attributes->set('_controller', \App\Controller\FindByPid\FranchiseController::class);
                return;
            }

            // Otherwise something has gone very wrong
            throw new NotFoundHttpException(sprintf('The item with PID "%s" was of an unknown type', $pid));
        }

        // Attempt to find a Version
        $version = $this->serviceFactory->getVersionsService()->findByPidFull($pid);
        if ($version) {
            $request->attributes->set('version', $version);
            $request->attributes->set('_controller', \App\Controller\FindByPid\VersionController::class);
            return;
        }

        // Attempt to find a Segment
        $segment = $this->serviceFactory->getSegmentsService()->findByPidFull($pid);
        if ($segment) {
            $request->attributes->set('segment', $segment);
            $request->attributes->set('_controller', \App\Controller\FindByPid\SegmentController::class);
            return;
        }

        throw new NotFoundHttpException(sprintf('The item with PID "%s" was not found', $pid));
    }
}
