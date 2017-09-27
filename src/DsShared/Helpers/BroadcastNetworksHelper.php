<?php
declare(strict_types=1);

namespace App\DsShared\Helpers;

use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Service;

class BroadcastNetworksHelper
{
    /** @var TranslateProvider */
    protected $translateProvider;

    public function __construct(TranslateProvider $translateProvider)
    {
        $this->translateProvider = $translateProvider;
    }

    /**
     * @param CollapsedBroadcast $collapsedBroadcast
     * @return string[] An associative array where the key is the network name (prefixed with the & or , (or not))
     *                  and the value is a string with the list of services belonging to the network joined by & or ,
     *                  and qualified with 'only' or 'except' (or not)
     *                  e.g. ['BBC One' => 'except Wales & Wales HD', '& BBC Two' => 'Wales']
     *                  If there are more than 5 networks or services, these get replaced by a string saying
     *                  'and X more...' where X is the number of networks or services minus 5
     */
    public function getNetworksAndServicesDetails(CollapsedBroadcast $collapsedBroadcast): array
    {
        $networkBreakdowns = $this->getCollapsedBroadcastNetworkBreakdown($collapsedBroadcast);
        return array_combine($this->buildNetworkNames($networkBreakdowns), $this->buildServicesNames($networkBreakdowns));
    }

    /**
     * @param array[] $networkBreakdowns
     * @return string[]
     */
    public function buildNetworkNames(array $networkBreakdowns): array
    {
        $networkNames = [];

        foreach ($networkBreakdowns as $networkBreakdown) {
            $networkNames[] = $networkBreakdown['network']->getName();
        }

        return $this->prefixNames($networkNames);
    }

    /**
     * @param array[] $networkBreakdowns
     * @return string[]
     */
    public function buildServicesNames(array $networkBreakdowns): array
    {
        $serviceNames = [];

        foreach ($networkBreakdowns as $networkBreakdown) {
            $broadcastOnServices = $networkBreakdown['on_services'];
            $notBroadcastOnServices = $networkBreakdown['not_on_services'];

            if (count($broadcastOnServices) === 1) {
                // If the broadcast happens only in one service just use the name without the 'only' qualifier
                $serviceNames[] = $broadcastOnServices[0]->getShortName();
            } elseif (!$notBroadcastOnServices) {
                // If the broadcast is present in all services, we don't need to qualify it
                $serviceNames[] = '';
            } else {
                // Always use the smallest number of services possible
                if (count($notBroadcastOnServices) < count($broadcastOnServices)) {
                    $services = $notBroadcastOnServices;
                    $translation = 'broadcast_except';
                } else {
                    $services = $broadcastOnServices;
                    $translation = 'broadcast_only_on';
                }

                $names = [];
                foreach ($services as $service) {
                    $names[] = $service->getShortName();
                }

                // Return all services with the relevant qualifier ('only' or 'except')
                $serviceNames[] = $this->translateProvider->getTranslate()->translate(
                    $translation,
                    ['%1' => implode('', $this->prefixNames($names))]
                );
            }
        }

        return $serviceNames;
    }

    /**
     * @param CollapsedBroadcast $collapsedBroadcast
     * @return array an associative array containing 3 indexes:
     *               'network' => the network
     *               'on_services' => services from the network where the broadcast happened
     *               'not_on_services' => services from the network where the broadcast didn't happened
     */
    public function getCollapsedBroadcastNetworkBreakdown(CollapsedBroadcast $collapsedBroadcast): array
    {
        $networksAndServices = [];

        // Build list of network and the services from that network where the broadcast happened
        foreach ($collapsedBroadcast->getServices() as $service) {
            if ($service->getNetwork()) {
                $nid = (string) $service->getNetwork()->getNid();
                if (!array_key_exists($nid, $networksAndServices)) {
                    $networksAndServices[$nid] = [
                        'network' => $service->getNetwork(),
                        'on_services' => [],
                    ];
                }

                $networksAndServices[$nid]['on_services'][(string) $service->getSid()] = $service;
            }
        }

        // Build a list of the services from a network where the broadcast didn't happen
        foreach ($networksAndServices as $nid => $networkAndServices) {
            $networksAndServices[$nid]['not_on_services'] = array_udiff(
                $networkAndServices['network']->getServices(),
                $networkAndServices['on_services'],
                function (Service $a, Service $b) {
                    return strcmp((string) $a->getSid(), (string) $b->getSid());
                }
            );

            $networksAndServices[$nid]['on_services'] = array_values($networksAndServices[$nid]['on_services']);
        }

        return array_values($networksAndServices);
    }

    private function prefixNames(array $names): array
    {
        $namesCount = count($names);

        // If there are more than 5 names, use only the first five names and attach an 'and X more' qualifier at the end
        if ($namesCount > 5) {
            $names = array_slice($names, 0, 5);
            $names[] = $this->translateProvider->getTranslate()->translate('x_more', ['%1' => $namesCount - 5]) . '...';
            $namesCount = 6;
        }

        // Attach a comma to the beginning of each name, except for the first and last one
        for ($i = 1; $i < $namesCount - 1; $i++) {
            $names[$i] = ', ' . $names[$i];
        }

        // Attach an ampersand to the beginning of the last name
        if ($namesCount > 1) {
            $names[$namesCount - 1] = ' & ' . $names[$namesCount - 1];
        }

        return $names;
    }
}
