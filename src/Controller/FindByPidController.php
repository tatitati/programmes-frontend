<?php
declare(strict_types = 1);
namespace AppBundle\Controller;

use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use Symfony\Component\HttpFoundation\Request;

class FindByPidController extends BaseController
{
    public function showAction(Pid $pid, ProgrammesService $programmesService)
    {
        // TODO: Name this method __invoke rather than showAction if
        // "controller.service_arguments" become supported on invokable controllers
        // https://github.com/symfony/symfony/issues/22202
        // $programmeCount = 2 ?? $programmesService->countAll();

        // Attempt to find a Programme
        // TODO lookup programme or group in one request
        $programme = $programmesService->findByPidFull($pid);
        if ($programme) {
            return $this->programmeResponse($programme);
        }

        // Attempt to find a Version
        // $version = $versionsService->findByPidFull($pid);
        // if ($version) {
        //     return $this->versionResponse($version);
        // }

        // Attempt to find a Segment
        // $segment = $segmentsService->findByPidFull($pid);
        // if ($segment) {
        //     return $this->segmentResponse($segment);
        // }

        // Attempt to find a SegmentEvent
        // $segmentEvent = $segmentEventsService->findByPidFull($pid);
        // if ($segmentEvent) {
        //     return $this->segmentEventResponse($segmentEvent);
        // }

        throw $this->createNotFoundException(sprintf('The item with PID "%s" was not found', $pid));
    }

    private function programmeResponse(Programme $programme)
    {
        return $this->renderWithChrome('@App/find_by_pid/programme.html.twig', [
            'programme' => $programme,
        ]);
    }
}
