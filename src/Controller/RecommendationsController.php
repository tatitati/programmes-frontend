<?php
declare(strict_types = 1);
namespace App\Controller;

use App\Controller\BaseController;
use App\ExternalApi\RecEng\Service\RecEngService;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Service\ProgrammesAggregationService;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use GuzzleHttp\Promise\FulfilledPromise;

class RecommendationsController extends BaseController
{
    public function __invoke(
        Programme $programme,
        string $extension,
        RecEngService $recEngService,
        ProgrammesAggregationService $programmeAggregationService,
        CollapsedBroadcastsService $collapsedBroadcastsService
    ) {
        $this->setContextAndPreloadBranding($programme);

        // Show 10 items when rendering the main page, show 2 on includes
        $limit = ($extension === '' ? 10 : 2);

        $episode = $this->getEpisode(
            $programme,
            $programmeAggregationService,
            $collapsedBroadcastsService
        );

        $promises = [
            'recommendations' => new FulfilledPromise([]),
        ];
        if ($episode) {
            $promises['recommendations'] = $recEngService->getRecommendations($episode, $limit);
        }

        if ($extension === '') {
            // We haven't got around to rendering the main route yet,
            // replace this with a call to $this->renderWithChrome later.
            throw $this->createNotFoundException('No such page');
        }

        return $this->renderWithoutChrome(
            'recommendations/show' . $extension . '.html.twig',
            array_merge($this->resolvePromises($promises), [
                'programme' => $programme,
            ])
        );
    }

    /**
     * recEng requires an Episode pid, so this determines which to use based on the type of Programme passed into it
     * Takes nullable args of latest, upcoming and last on episodes as this is called in TLEC controller and these are already fetched
     */
    private function getEpisode(
        Programme $programme,
        ProgrammesAggregationService $programmeAggregationService,
        CollapsedBroadcastsService $collapsedBroadcastsService
    ): ?Episode {
        if ($programme instanceof Episode) {
            return $programme;
        }

        if ($programme instanceof Clip) {
            if ($programme->getParent() && $programme->getParent() instanceof Episode) {
                return $programme->getParent();
            }
        }

        if ($programme instanceof ProgrammeContainer && $programme->getAggregatedEpisodesCount()) {
            if ($programme->getAvailableEpisodesCount()) {
                $onDemandEpisodes = $programmeAggregationService->findStreamableOnDemandEpisodes($programme, 1);
                if ($onDemandEpisodes) {
                    return $onDemandEpisodes[0];
                }
            }

            $upcomingBroadcast = $collapsedBroadcastsService->findNextDebutOrRepeatOnByProgramme($programme);
            if ($upcomingBroadcast) {
                return $upcomingBroadcast[0]->getProgrammeItem();
            }

            $lastOnBroadcast = $collapsedBroadcastsService->findPastByProgramme($programme, 1);
            if ($lastOnBroadcast) {
                return $lastOnBroadcast[0]->getProgrammeItem();
            }
        }

        return null;
    }
}
