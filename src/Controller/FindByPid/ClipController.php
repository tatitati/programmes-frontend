<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use App\Controller\Helpers\StructuredDataHelper;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Service\GroupsService;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Service\RelatedLinksService;
use Cake\Chronos\ChronosInterval;

class ClipController extends BaseController
{
    public function __invoke(
        Clip $clip,
        GroupsService $groupsService,
        RelatedLinksService $relatedLinksService,
        StructuredDataHelper $structuredDataHelper
    ) {
        $this->setIstatsProgsPageType('programmes_clip');
        $this->setIstatsReleaseDate($clip);
        $this->setIstatsReleaseYear($clip);
        $this->setParentIstats($clip);
        $this->setContextAndPreloadBranding($clip);

        $relatedLinks = [];
        if ($clip->getRelatedLinksCount() > 0) {
            $relatedLinks = $relatedLinksService->findByRelatedToProgramme($clip, ['related_site', 'miscellaneous']);
        }

        $featuredIn = $groupsService->findByCoreEntityMembership($clip, 'Collection');

        return $this->renderWithChrome('find_by_pid/clip.html.twig', [
            'programme' => $clip,
            'featuredIn' => $featuredIn,
            'schema' => $this->getSchema($structuredDataHelper, $clip),
            'relatedLinks' => $relatedLinks,
        ]);
    }

    private function setIstatsReleaseDate(Clip $clip): void
    {
        if ($clip->getReleaseDate()) {
            $this->setIstatsExtraLabels(['clip_release_date' => $clip->getReleaseDate()->asDateTime()->format('c')]);
        } elseif ($clip->getStreamableFrom()) {
            $this->setIstatsExtraLabels(['clip_release_date' => $clip->getStreamableFrom()->format('c')]);
        }
    }

    private function setIstatsReleaseYear(Clip $clip): void
    {
        if ($clip->getReleaseDate()) {
            $this->setIstatsExtraLabels(['clip_release_year' => $clip->getReleaseDate()->asDateTime()->format('Y')]);
        } elseif ($clip->getStreamableFrom()) {
            $this->setIstatsExtraLabels(['clip_release_year' => $clip->getStreamableFrom()->format('Y')]);
        }
    }

    private function setParentIstats(Clip $clip): void
    {
        $parent = $clip->getParent();
        if ($parent instanceof ProgrammeItem) {
            $this->setIstatsExtraLabels(['parent_available' => $parent->isStreamable() ? 'true' : 'false']);
            $this->setIstatsExtraLabels(['parent_entity_type' => $parent->getType()]);
        }
    }

    private function getSchema(
        StructuredDataHelper $structuredDataHelper,
        Clip $clip
    ): array {
        $clipSchema = $structuredDataHelper->buildSchemaForClip($clip);
        $parent = $clip->getParent();

        if ($parent instanceof Episode) {
            $clipSchema['partOfEpisode'] = $structuredDataHelper->getSchemaForEpisode($parent, true);
        } elseif ($parent instanceof ProgrammeContainer) {
            if ($parent->isTlec()) {
                $clipSchema['partOfSeries'] = $structuredDataHelper->getSchemaForProgrammeContainer($parent);
            } else {
                $clipSchema['partOfSeries'] = $structuredDataHelper->getSchemaForProgrammeContainer($parent->getTleo());
                $clipSchema['partOfSeason'] = $structuredDataHelper->getSchemaForProgrammeContainer($parent);
            }
        }

        $duration = new ChronosInterval(null, null, null, null, null, null, $clip->getDuration());
        $clipSchema['timeRequired'] = (string) $duration;

        if ($clip->getStreamableUntil()) {
            $clipSchema['expires'] = $clip->getStreamableUntil();
        }

        $genres = $clip->getGenres();
        if ($genres) {
            $clipSchema['genre'] = array_map(function ($genre) {
                return $genre->getUrlKeyHierarchy();
            }, $genres);
        }

        return $clipSchema;
    }
}
