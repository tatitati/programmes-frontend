<?php
declare(strict_types=1);

namespace App\Controller\Podcast;

use App\Controller\BaseController;
use App\Ds2013\PresenterFactory;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
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
    public function __invoke(Programme $programme)
    {
        if (!$programme->isTleo()) {
            return $this->cachedRedirectToRoute('programme_podcast_episodes_download', ['pid'=>$programme->getTleo()->getPid()], 301);
        }

        $this->setContextAndPreloadBranding($programme);

        $this->overridenDescription = 'Podcast downloads for ' . $programme->getTitle();

        return $this->renderWithChrome('podcast/podcast.html.twig', [
            'programme' => $programme,
        ]);
    }
}
