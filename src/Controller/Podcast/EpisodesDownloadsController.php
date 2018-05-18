<?php
declare(strict_types=1);

namespace App\Controller\Podcast;

use App\Controller\BaseController;
use App\Ds2013\PresenterFactory;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Podcast full page. Future implementation.
 *
 * When a user click in podcast panel, it takes you to this full page.
 */
class EpisodesDownloadsController extends BaseController
{
    public function __invoke(string $pid, ProgrammesService $programmesService, PresenterFactory $presenterFactory)
    {
        $programme = $programmesService->findByPidFull(new Pid($pid));
        if (!$programme) {
            throw new NotFoundHttpException(sprintf('Unknown Programme with PID "%s"', $pid));
        }

        $this->setContextAndPreloadBranding($programme);

        return $this->renderWithChrome('podcast/podcast.html.twig');
    }
}
