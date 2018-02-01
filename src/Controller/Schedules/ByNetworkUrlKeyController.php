<?php
namespace App\Controller\Schedules;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Service\NetworksService;

class ByNetworkUrlKeyController extends BaseController
{
    public function __invoke(NetworksService $networkService, $networkUrlKey)
    {
        $network = $networkService->findByUrlKeyWithDefaultService($networkUrlKey);

        if (is_null($network) || !$network->getDefaultService()) {
            throw $this->createNotFoundException('Network not found');
        }

        return $this->cachedRedirectToRoute('schedules_by_day', ['pid' => (string) $network->getDefaultService()->getPid()], 301);
    }
}
