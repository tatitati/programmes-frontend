<?php
declare(strict_types = 1);

namespace App\ExternalApi\Electron\Service;

use App\ExternalApi\Client\HttpApiClient;
use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\Electron\Domain\SupportingContentItem;
use App\ExternalApi\Electron\Mapper\SupportingContentMapper;
use App\ExternalApi\Exception\MultiParseException;
use App\ExternalApi\XmlParser\XmlParser;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use SimpleXMLElement;

class ElectronService
{
    /** @var HttpApiClientFactory */
    private $clientFactory;

    /** @var XmlParser */
    private $xmlParser;

    /** @var SupportingContentMapper */
    private $supportingContentMapper;

    /** @var string */
    private $baseUrl;

    public function __construct(
        HttpApiClientFactory $clientFactory,
        XmlParser $xmlParser,
        SupportingContentMapper $supportingContentMapper,
        string $baseUrl
    ) {
        $this->clientFactory = $clientFactory;
        $this->xmlParser = $xmlParser;
        $this->supportingContentMapper = $supportingContentMapper;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param Programme $programme
     * @return PromiseInterface ( Promise returns SupportingContentItem[] when unwrapped)
     */
    public function fetchSupportingContentItemsForProgramme(Programme $programme): PromiseInterface
    {
        $client = $this->makeClient($programme);
        return $client->makeCachedPromise();
    }

    private function makeClient(Programme $programme): HttpApiClient
    {
        $cacheKey = $this->clientFactory->keyHelper(__CLASS__, __FUNCTION__, (string) $programme->getPid());
        $url = $this->makeSupportingContentUrlForProgramme($programme->getPid());

        return $this->clientFactory->getHttpApiMultiClient(
            $cacheKey,
            [$url],
            Closure::fromCallable([$this, 'parseResponse'])
        );
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
     * @param Response[] $responses
     * @return SupportingContentItem[]
     */
    private function parseResponse(array $responses): array
    {
        $responseBody = $responses[0]->getBody()->getContents();
        $simpleXml = $this->xmlParser->parse($responseBody);
        if (!isset($simpleXml->content->pages)) {
            throw new MultiParseException(0, "Electron XML response does not contain pages element");
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
