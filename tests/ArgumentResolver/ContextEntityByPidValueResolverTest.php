<?php
declare(strict_types = 1);
namespace Tests\App\ArgumentResolver;

use App\ArgumentResolver\ContextEntityByPidValueResolver;
use App\Exception\ProgrammeOptionsRedirectHttpException;
use BBC\ProgrammesPagesService\Domain\Entity\Group;
use BBC\ProgrammesPagesService\Domain\Entity\Options;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\CoreEntitiesService;
use BBC\ProgrammesPagesService\Service\ServiceFactory;
use BBC\ProgrammesPagesService\Service\ServicesService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContextEntityByPidValueResolverTest extends TestCase
{
    /** @var ArgumentResolver */
    private $resolver;

    /** @var CoreEntitiesService */
    private $coreEntitiesService;

    /** @var ServicesService */
    private $servicesService;

    public function setUp()
    {
        $this->coreEntitiesService = $this->createMock(CoreEntitiesService::class);
        $this->servicesService = $this->createMock(ServicesService::class);

        $serviceFactory = $this->createMock(ServiceFactory::class);
        $serviceFactory->method('getCoreEntitiesService')->willReturn($this->coreEntitiesService);
        $serviceFactory->method('getServicesService')->willReturn($this->servicesService);

        $this->resolver = new ArgumentResolver(null, [
            new ContextEntityByPidValueResolver($serviceFactory),
        ]);
    }

    public function testResolveProgramme()
    {
        $request = Request::create('/');
        $request->attributes->set('pid', 'b0000001');
        $controller = function (Programme $pid) {
        };

        $programme = $this->createMock(Programme::class);

        $this->coreEntitiesService->expects($this->once())->method('findByPidFull')
            ->with(new Pid('b0000001'), 'Programme')
            ->willReturn($programme);

        $this->servicesService->expects($this->never())->method('findByPidFull');

        $this->assertEquals(
            [$programme],
            $this->resolver->getArguments($request, $controller)
        );
    }

    public function testResolveGroup()
    {
        $request = Request::create('/');
        $request->attributes->set('pid', 'b0000001');
        $controller = function (Group $pid) {
        };

        $group = $this->createMock(Group::class);

        $this->coreEntitiesService->expects($this->once())->method('findByPidFull')
            ->with(new Pid('b0000001'), 'Group')
            ->willReturn($group);

        $this->servicesService->expects($this->never())->method('findByPidFull');

        $this->assertEquals(
            [$group],
            $this->resolver->getArguments($request, $controller)
        );
    }

    public function testResolveService()
    {
        $request = Request::create('/');
        $request->attributes->set('pid', 'b0000001');
        $controller = function (Service $pid) {
        };

        $service = $this->createMock(Service::class);

        $this->coreEntitiesService->expects($this->never())->method('findByPidFull');

        $this->servicesService->expects($this->once())->method('findByPidFull')
            ->with(new Pid('b0000001'))
            ->willReturn($service);

        $this->assertEquals(
            [$service],
            $this->resolver->getArguments($request, $controller)
        );
    }

    public function testResolveOfUnfoundEntityThrows404()
    {
        $request = Request::create('/');
        $request->attributes->set('pid', 'b0000001');
        $controller = function (Programme $pid) {
        };

        $this->coreEntitiesService->expects($this->once())->method('findByPidFull')
            ->with(new Pid('b0000001'), 'Programme')
            ->willReturn(null);

        $this->servicesService->expects($this->never())->method('findByPidFull');

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('The item of type "' . Programme::class . '" with PID "b0000001" was not found');

        $this->resolver->getArguments($request, $controller);
    }

    public function testResolveOfEnityWithRedirectsThrowsARedirect()
    {
        $programme = $this->createMock(Programme::class);
        $programme->method('getOptions')->willReturn(new Options([
            'pid_override_url' => 'http://example.com',
            'pid_override_code' => 301,
        ]));

        $request = Request::create('/');
        $request->attributes->set('pid', 'b0000001');
        $controller = function (Programme $pid) {
        };

        $this->coreEntitiesService->expects($this->once())->method('findByPidFull')
            ->with(new Pid('b0000001'), 'Programme')
            ->willReturn($programme);

        $this->servicesService->expects($this->never())->method('findByPidFull');

        $this->expectException(ProgrammeOptionsRedirectHttpException::class);
        $this->expectExceptionMessage('Programme Options has triggered a "301" redirect to "http://example.com"');

        $this->resolver->getArguments($request, $controller);
    }
}
