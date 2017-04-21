<?php
declare(strict_types = 1);
namespace App\Controller;

use BBC\ProgrammesPagesService\Service\NetworksService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;

class HomeController extends BaseController
{
    public function __invoke(ProgrammesService $programmesService, NetworksService $networksService)
    {
        $programmeCount = $programmesService->countAll();

        $serviceTypes = ['TV', 'National Radio', 'Regional Radio', 'Local Radio'];
        $networks = $networksService->findPublishedNetworksByType($serviceTypes, NetworksService::NO_LIMIT);

        return $this->renderWithChrome('home/show.html.twig', [
            'programmeCount' => $programmeCount,
            'tvNetworks' => $this->filterNetworks($networks, 'TV'),
            'nationalRadioNetworks' => $this->filterNetworks($networks, 'National Radio'),
            'regionalRadioNetworks' => $this->filterNetworks($networks, 'Regional Radio'),
            'localRadioNetworks' => $this->filterNetworks($networks, 'Local Radio'),
        ]);
    }

    private function filterNetworks(array $networks, string $type)
    {
        // We don't need to sort after filtering as the array of
        // networks was already in order
        return  array_filter($networks, function ($network) use ($type) {
            return $network->getType() == $type;
        });
    }
}
