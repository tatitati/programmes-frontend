<?php
declare(strict_types = 1);
namespace App\Controller;

use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Service\ProgrammesService;

class FindByPidController extends BaseController
{
    public function __invoke(Pid $pid, ProgrammesService $programmesService)
    {
        // Attempt to find a Programme
        // TODO lookup programme or group in one request
        $programme = $programmesService->findByPidFull($pid);
        if ($programme) {
            $this->setContext($programme);
            return $this->exampleProgrammeResponse($programme);
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

    /**
     * I am a placeholder so we can show a page for programmes that we have not
     * yet started working on. I can be removed once we implement all programme
     * routes
     */
    private function exampleProgrammeResponse(Programme $programme)
    {
        return $this->renderWithChrome('find_by_pid/example_programme.html.twig', [
            'programme' => $programme,
        ]);
    }
}
