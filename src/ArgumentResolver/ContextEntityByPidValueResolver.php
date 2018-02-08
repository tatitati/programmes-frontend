<?php
declare(strict_types = 1);
namespace App\ArgumentResolver;

use App\Exception\ProgrammeOptionsRedirectHttpException;
use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Collection;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Franchise;
use BBC\ProgrammesPagesService\Domain\Entity\Gallery;
use BBC\ProgrammesPagesService\Domain\Entity\Group;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Season;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\ServiceFactory;
use Generator;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * If a controller has an argument that is a Programme, Group or Service (or
 * one of their subclasses) and the route has an argument called 'pid' then
 * attempt to look up that pid and inject the returned entity into the
 * controller.
 *
 * This shall throw a 404 if an entity of the given type and PID does not exist.
 */
class ContextEntityByPidValueResolver implements ArgumentValueResolverInterface
{
    // Explicitly define all subtypes of CoreEntity here, to avoid having to
    // run an is_a() check which would be more expensive than an in_array()
    private const SUPPORTED_CLASSES = [
        // CoreEntity & child classes
        CoreEntity::class,
        Programme::class,
        ProgrammeContainer::class,
        ProgrammeItem::class,
        Brand::class,
        Series::class,
        Episode::class,
        Clip::class,
        Group::class,
        Collection::class,
        Gallery::class,
        Franchise::class,
        Season::class,
        // Service
        Service::class,
    ];

    private $serviceFactory;

    public function __construct(ServiceFactory $serviceFactory)
    {
        $this->serviceFactory = $serviceFactory;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return ($request->attributes->has('pid') && in_array($argument->getType(), self::SUPPORTED_CLASSES) && !$argument->isVariadic());
    }

    public function resolve(Request $request, ArgumentMetadata $argument): Generator
    {
        $type = $argument->getType();
        $pid = new Pid($request->attributes->get('pid'));
        $entity = null;

        if (is_a($type, CoreEntity::class, true)) {
            // Attempt to look up the CoreEntity. Filter to only return the
            // type of entity requested

            // Conveniently the filter to pass into findByPidFull is the short
            // class name of the entity i.e. without the namespace
            $entity = $this->serviceFactory->getCoreEntitiesService()->findByPidFull(
                $pid,
                (new ReflectionClass($type))->getShortName()
            );

            if ($entity instanceof Franchise) {
                throw new NotFoundHttpException(sprintf('The item with PID "%s" was a franchise, which v3 does not support', $pid));
            }

            // Redirect if the options demand it
            if ($entity && $entity->getOptions()->getOption('pid_override_url') && $entity->getOptions()->getOption('pid_override_code')) {
                throw new ProgrammeOptionsRedirectHttpException(
                    $entity->getOptions()->getOption('pid_override_url'),
                    $entity->getOptions()->getOption('pid_override_code')
                );
            }
        } elseif (is_a($type, Service::class, true)) {
            // Attempt to look up the Service
            $entity = $this->serviceFactory->getServicesService()->findByPidFull($pid);
        }

        if (!$entity) {
            throw new NotFoundHttpException(sprintf(
                'The item of type "%s" with PID "%s" was not found',
                $type,
                $pid
            ));
        }

        yield $entity;
    }
}
