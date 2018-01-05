<?php
declare(strict_types = 1);

namespace App\ExternalApi\Electron\Service;

use App\ExternalApi\Electron\Domain\SupportingContentItem;
use App\ExternalApi\Electron\Mapper\SupportingContentMapper;
use App\ExternalApi\XmlParser\XmlParser;
use App\ExternalApi\Exception\ParseException;
use BBC\ProgrammesPagesService\Cache\CacheInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;

class ElectronService
{
    /** @var ClientInterface */
    private $client;

    /** @var CacheInterface */
    private $cache;

    /** @var XmlParser */
    private $xmlParser;

    /** @var SupportingContentMapper */
    private $supportingContentMapper;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $baseUrl;

    public function __construct(
        ClientInterface $client,
        CacheInterface $cache,
        XmlParser $xmlParser,
        SupportingContentMapper $supportingContentMapper,
        LoggerInterface $logger,
        string $baseUrl
    ) {
        $this->client = $client;
        $this->cache = $cache;
        $this->xmlParser = $xmlParser;
        $this->supportingContentMapper = $supportingContentMapper;
        $this->logger = $logger;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param Programme $programme
     * @return SupportingContentItem[]
     */
    public function fetchSupportingContentItemsForProgramme(Programme $programme): array
    {
        $cacheKey = $this->cache->keyHelper(__CLASS__, __FUNCTION__, (string) $programme->getPid());
        $cacheItem = $this->cache->getItem($cacheKey);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }
        $url = $this->makeSupportingContentUrlForProgramme($programme->getPid());
        try {
            $response = $this->client->request('GET', $url);
        } catch (GuzzleException $e) {
            if ($e instanceof ClientException && $e->getResponse() && $e->getResponse()->getStatusCode() === 404) {
                // 404s get cached for a shorter time
                $this->cache->setItem($cacheItem, [], CacheInterface::NORMAL);
            }
            return [];
        }
        try {
            $result = $this->parseResponseBody($response->getBody()->getContents());
        } catch (ParseException $e) {
            $this->logger->error("Error parsing Electron XML for $url . Error was: " . $e->getMessage());
            return [];
        }
        $this->cache->setItem($cacheItem, $result, CacheInterface::MEDIUM);
        return $result;
    }

    private function makeSupportingContentUrlForProgramme(Pid $pid): string
    {
        $pid = (string) $pid;
        $lastLetterOfPid = substr($pid, -1);
        $url = $this->baseUrl . '/atom/v1/programmessupportingcontent';
        $url .= '/' . urlencode($lastLetterOfPid) . '/' . urlencode($pid);
        return $url;
    }

    /**
     * @param string $responseBody
     * @return SupportingContentItem[]
     */
    private function parseResponseBody(string $responseBody): array
    {
        $simpleXml = $this->xmlParser->parse($responseBody);
        if (!isset($simpleXml->content->pages)) {
            throw new ParseException("Electron XML response does not contain pages element");
        }
        return $this->mapItems($simpleXml->content->pages);
    }

    /**
     * @param SimpleXMLElement $pages
     * @return SupportingContentItem[]
     */
    private function mapItems(SimpleXMLElement $pages): array
    {
        $supportingContentItems = [];
        foreach ($pages->page as $page) {
            $item = $this->supportingContentMapper->mapItem($page);
            if ($item) {
                $supportingContentItems[] = $item;
            }
        }
        return $supportingContentItems;
    }
}
