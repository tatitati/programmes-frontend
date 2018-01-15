<?php
declare(strict_types = 1);
namespace App\Controller\ProgrammeEpisodes;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Service\ProgrammesAggregationService;

class PlayerController extends BaseController
{
    public function __invoke(
        ProgrammeContainer $programme,
        ProgrammesAggregationService $programmeAggregationService
    ) {
        $this->setContext($programme);
        $page = $this->getPage();
        $limit = 10;

        $availableEpisodes = $programmeAggregationService->findStreamableOnDemandEpisodes(
            $programme,
            $limit,
            $page
        );

        // If you visit an out-of-bounds page then throw a 404. Page one should
        // always be a 200 so search engines don't drop their reference to the
        // page while a programme is off-air
        if (!$availableEpisodes && $page !== 1) {
            throw $this->createNotFoundException('Page does not exist');
        }

        return $this->renderWithChrome('programme_episodes/player.html.twig', [
            'programme' => $programme,
            'availableEpisodes' => $availableEpisodes,
        ]);
    }
}
