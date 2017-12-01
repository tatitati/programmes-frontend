<?php
namespace App\Controller;

use BBC\ProgrammesPagesService\Service\NetworksService;

class SchedulesByNetworkUrlKeyController extends BaseController
{
    public function __invoke(NetworksService $networkService, $networkUrlKey)
    {
        $network = $networkService->findByUrlKeyWithDefaultService($networkUrlKey);

        if (is_null($network) || !$network->getDefaultService()) {
            throw $this->createNotFoundException('Network not found');
        }

        return $this->redirectToRoute('schedules_by_day', ['pid' => (string) $network->getDefaultService()->getPid()], 301);
    }
}
