<?php
declare(strict_types = 1);
namespace App\Controller;

use BBC\ProgrammesPagesService\Service\NetworksService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

class HomeController extends BaseController
{
    public function __invoke(EntityManagerInterface $em, NetworksService $networksService)
    {
        $programmeCount = $this->countProgrammesAndGroups($em);

        $serviceTypes = ['TV', 'National Radio', 'Regional Radio', 'Local Radio'];
        $networks = $networksService->findPublishedNetworksByType(
            $serviceTypes,
            NetworksService::NO_LIMIT
        );

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
        return array_filter($networks, function ($network) use ($type) {
            return $network->getType() == $type;
        });
    }

    private function countProgrammesAndGroups(EntityManagerInterface $em)
    {
        // This is very naughty, as all Programmes query logic should be in
        // programmes-pages-service. But we'll make an exception here as it
        // really is a one-off.
        // Use a native query here because providing an accurate value by
        // excluding embargoed items takes too long, and it's not worth adding
        // an index to the table for this one query.
        $qText = "SELECT COUNT(*) as cnt FROM core_entity c";

        $rms = new ResultSetMapping();
        $rms->addScalarResult('cnt', 'cnt');

        return $em->createNativeQuery($qText, $rms)->getSingleScalarResult();
    }
}
