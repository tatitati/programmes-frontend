<?php
declare(strict_types=1);

namespace App\Controller\Podcast;

use App\Controller\BaseController;
use App\Ds2013\PresenterFactory;
use App\ExternalApi\RmsPodcast\Service\RmsPodcastService;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Podcast partial page.
 *
 * EpisodeController call to this controller by ajax in order to load podcast panel.
 */
class PodcastDs2013Controller extends BaseController
{
    public function __invoke(string $pid, ProgrammesService $programmesService, PresenterFactory $presenterFactory, RmsPodcastService $podcastService)
    {
        $programme = $programmesService->findByPidFull(new Pid($pid));

        if (!$this->isValidProgramme($programme)) {
            throw new NotFoundHttpException(sprintf('Unknown Podcast with PID "%s"', $pid));
        }

        $isPodcast = $podcastService->getPodcast($programme->getPid())->wait(true);

        if ($isPodcast) {
            return $this->render('podcast/podcast.2013inc.html.twig', [
                'podcast' => $programme,
            ], $this->response());
        }

        throw new NotFoundHttpException(sprintf('Unknown Podcast with PID "%s"', $pid));
    }

    private function isValidProgramme(?Programme $programme)
    {
        // users and episode page will request this partial and we don't want to display a 404 for that
        if (!$programme || !$programme->isTleo() || !$programme instanceof ProgrammeContainer) {
            return false;
        }

        return true;
    }
}
