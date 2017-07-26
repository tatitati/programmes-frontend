<?php
declare(strict_types = 1);
namespace Tests\App\EventSubscriber;

use App\EventSubscriber\FindByPidRouterSubscriber;
use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use BBC\ProgrammesPagesService\Service\SegmentsService;
use BBC\ProgrammesPagesService\Service\VersionsService;
use BBC\ProgrammesPagesService\Service\ServiceFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class FindByPidRouterSubscriberTest extends TestCase
{
    /**
     * @dataProvider entityDataProvider
     */
    public function testEntityResponse($programme, $version, $segment, $expectedController)
    {
        $request = $this->request();

        $this->buildSubscriber(
            $programme,
            $version,
            $segment
        )->updateController($this->event($request));

        $this->assertEquals($expectedController, $request->attributes->get('_controller'));

        if ($programme) {
            $this->assertEquals($programme, $request->attributes->get('programme'));
        } elseif ($version) {
            $this->assertEquals($version, $request->attributes->get('version'));
        } elseif ($segment) {
            $this->assertEquals($segment, $request->attributes->get('segment'));
        }
    }

    public function entityDataProvider()
    {
        $tleo = $this->createMock(Brand::class);
        $series = $this->createMock(Series::class);
        $series->method('getParent')->willReturn($tleo);

        return [
            [$tleo, null, null, 'App\Controller\FindByPid\TlecController'],
            [$series, null, null, 'App\Controller\FindByPid\DefaultController'],
            [$this->createMock(Episode::class), null, null, 'App\Controller\FindByPid\DefaultController'],
            [$this->createMock(Clip::class), null, null, 'App\Controller\FindByPid\DefaultController'],
            // TODO add checks for Groups
            [null, $this->createMock(Version::class), null, 'App\Controller\FindByPid\VersionController'],
        ];
        // TODO add checks for Groups and Segment
    }

    public function testOnlyRunsOnMasterRequests()
    {
        $request = $this->request();

        $this->buildSubscriber()->updateController($this->event($request, false));

        $this->assertEquals('!find_by_pid', $request->attributes->get('_controller'));
    }

    public function testOnlyRunsOnFindByPidRequests()
    {
        $request = new Request([], [], ['_controller' => 'zzz']);

        $this->buildSubscriber()->updateController($this->event($request, false));

        $this->assertEquals('zzz', $request->attributes->get('_controller'));
    }

    public function testThrowsExceptionIfNoResultsFound()
    {
        $request = $this->request();

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('The item with PID "b0000001" was not found');

        $this->buildSubscriber()->updateController($this->event($request));
    }

    private function buildSubscriber(
        Programme $programmeResult = null,
        Version $versionResult = null,
        Segment $segmentResult = null
    ) {
        $programmesService = $this->createMock(ProgrammesService::class);
        $programmesService->method('findByPidFull')->willReturn($programmeResult);

        $versionsService = $this->createMock(VersionsService::class);
        $versionsService->method('findByPidFull')->willReturn($versionResult);

        $segmentsService = $this->createMock(SegmentsService::class);
        $segmentsService->method('findByPidFull')->willReturn($segmentResult);

        $serviceFactory = $this->createMock(ServiceFactory::class);
        $serviceFactory->method('getProgrammesService')->willReturn($programmesService);
        $serviceFactory->method('getVersionsService')->willReturn($versionsService);
        $serviceFactory->method('getSegmentsService')->willReturn($segmentsService);

        return new FindByPidRouterSubscriber($serviceFactory);
    }

    private function request()
    {
        $attributes = ['pid' => 'b0000001', '_controller' => '!find_by_pid'];

        return new Request([], [], $attributes);
    }

    private function event(Request $request, bool $isMasterRequest = true)
    {
        return new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            $isMasterRequest ? HttpKernelInterface::MASTER_REQUEST : HttpKernelInterface::SUB_REQUEST
        );
    }
}
