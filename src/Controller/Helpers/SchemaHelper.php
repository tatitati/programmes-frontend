<?php
declare(strict_types = 1);

namespace App\Controller\Helpers;

use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\ChronosInterval;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SchemaHelper
{
    /** @var UrlGeneratorInterface */
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function getSchemaForBroadcast(Broadcast $broadcast): array
    {
        $programmeItem = $broadcast->getProgrammeItem();
        $episode = $this->getSchemaForEpisode($programmeItem);
        $episode['publication'] = [
            '@type' => 'BroadcastEvent',
            'publishedOn' => $this->getSchemaForService($broadcast->getService()),
            'startDate' => $broadcast->getStartAt()->format(DATE_ATOM),
            'endDate' => $broadcast->getEndAt()->format(DATE_ATOM),
        ];

        return $episode;
    }

    public function getSchemaForCollapsedBroadcast(CollapsedBroadcast $collapsedBroadcast): array
    {
        $programmeItem = $collapsedBroadcast->getProgrammeItem();
        $episode = $this->getSchemaForEpisode($programmeItem);
        $publishedOn = [];
        foreach ($collapsedBroadcast->getServices() as $service) {
            $publishedOn[] = $this->getSchemaForService($service);
        }
        $episode['publication'] = [
            '@type' => 'BroadcastEvent',
            'publishedOn' => $publishedOn,
            'startDate' => $collapsedBroadcast->getStartAt()->format(DATE_ATOM),
            'endDate' => $collapsedBroadcast->getEndAt()->format(DATE_ATOM),
        ];

        return $episode;
    }


    public function getSchemaForOnDemand(Episode $episode): array
    {
        $episodeContext = $this->getSchemaForEpisode($episode);
        $episodeContext['publication'] = [
            '@type' => 'OnDemandEvent',
            'publishedOn' => [
                '@type' => 'BroadcastService',
                'broadcaster' => $this->getSchemaForOrganisation(),
                'name' => 'iPlayer',
            ],
            'duration' => (string) new ChronosInterval(null, null, null, null, null, null, $episode->getDuration()),
            'url' => $this->router->generate('iplayer_play', ['pid' => $episode->getPid()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
        if ($episode->getStreamableFrom()) {
            $episodeContext['publication']['startDate'] = $episode->getStreamableFrom()->format(DATE_ATOM);
        }
        if ($episode->getStreamableUntil()) {
            $episodeContext['publication']['endDate'] = $episode->getStreamableUntil()->format(DATE_ATOM);
        }

        return $episodeContext;
    }

    public function getSchemaForSeries(ProgrammeContainer $programme): array
    {
        return [
            '@type' => $programme->isRadio() ? 'RadioSeries' : 'TVSeries',
            'image' => $programme->getImage()->getUrl(480),
            'description' => $programme->getShortSynopsis(),
            'identifier' => $programme->getPid(),
            'name' => $programme->getTitle(),
            'url' => $this->router->generate('find_by_pid', ['pid' => $programme->getPid()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }

    public function prepare($schemaToPrepare, $isArrayOfContexts = false): array
    {
        if ($isArrayOfContexts) {
            $schema = [
                '@context' => 'http://schema.org',
                '@graph' => $schemaToPrepare,
            ];
            return $schema;
        }

        $schemaToPrepare['@context'] = 'http://schema.org';

        return $schemaToPrepare;
    }

    private function getSchemaForEpisode(ProgrammeItem $programmeItem): array
    {
        $episode = [
            '@type' => $programmeItem->isRadio() ? 'RadioEpisode' : 'TVEpisode',
            'identifier' => $programmeItem->getPid(),
            'episodeNumber' => $programmeItem->getPosition(),
            'description' => $programmeItem->getShortSynopsis(),
            'datePublished' => $programmeItem->getReleaseDate(),
            'image' => $programmeItem->getImage()->getUrl(480),
            'name' => $programmeItem->getTitle(), //@TODO This will be JUST the episode name, or so we want the fully qualified name?
            'url' => $this->router->generate('find_by_pid', ['pid' => $programmeItem->getPid()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
        $parent = $programmeItem->getParent();
        if ($parent) {
            if ($parent->isTlec()) {
                $episode['partOfSeries'] = [
                    '@type' => $programmeItem->isRadio() ? 'RadioSeries' : 'TVSeries',
                    'name' => $parent->getTitle(),
                    'url' => $this->router->generate('find_by_pid', ['pid' => $parent->getPid()], UrlGeneratorInterface::ABSOLUTE_URL),
                ];
            } else {
                $episode['partOfSeries'] = [
                    '@type' => $programmeItem->isRadio() ? 'RadioSeries' : 'TVSeries',
                    'name' => $parent->getParent()->getTitle(),
                    'url' => $this->router->generate('find_by_pid', ['pid' => $parent->getParent()->getPid()], UrlGeneratorInterface::ABSOLUTE_URL),
                ];
                $episode['partOfSeason'] = [
                    '@type' => $programmeItem->isRadio() ? 'RadioSeason' : 'TVSeason',
                    'position' => $parent->getPosition(),
                    'name' => $parent->getTitle(),
                    'url' => $this->router->generate('find_by_pid', ['pid' => $parent->getPid()], UrlGeneratorInterface::ABSOLUTE_URL),
                ];
            }
        }
        return $episode;
    }

    private function getSchemaForOrganisation(): array
    {
        return [
            '@type' => 'Organization',
            'legalName' => 'British Broadcasting Corporation',
            'logo' => 'http://ichef.bbci.co.uk/images/ic/1200x675/p01tqv8z.png',
            'name' => 'BBC',
            'url' => 'https://www.bbc.co.uk/',
        ];
    }

    private function getSchemaForService(Service $service): array
    {
        $bbcContext = $this->getSchemaForOrganisation();
        $serviceContext = [
            '@type' => 'BroadcastService',
            'broadcaster' => $bbcContext,
            'name' => $service->getName(),
        ];
        $network = $service->getNetwork();
        if ($network !== null && $network->getName() !== $service->getName()) {
            $networkContext = [
                '@type' => 'BroadcastService',
                'broadcaster' => $bbcContext,
                'name' => $network->getName(),
                'logo' => $network->getImage()->getUrl(480),
            ];
            $serviceContext['parentService'] = $networkContext;
        }

        return $serviceContext;
    }
}
