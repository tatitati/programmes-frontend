<?php
declare(strict_types=1);

namespace App\ExternalApi\Ada\Service;

use App\ExternalApi\Ada\Domain\AdaClass;
use App\ExternalApi\Ada\Mapper\AdaClassMapper;
use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\Client\HttpApiMultiClient;
use App\ExternalApi\Exception\MultiParseException;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;

/**
 * For querying Ada Classes, which are the overarching containers that
 * ProgrammeItems may be related to.
 *
 * For more info see: https://confluence.dev.bbc.co.uk/display/ADA/API+Specification
 *
 * In the API there are two types of class: Tag and Category however we do not
 * differentiate between these on the front end. This means that when we reqest
 * data we request twice as many results as we need because we may have to
 * deduplicate Tags and Categories with the same ID.
 *
 * e.g. If we want 2 items, we have to request 4 items as we may get this
 * result set:
 *
 * [
 *   {"id":"one", type:"tag"},
 *   {"id":"one", type:"category"},
 *   {"id":"two", type:"tag"},
 *   {"id":"two", type:"category"}
 * ]
 *
 * Which we would then deduplicate into the returned data:
 * [
 *   new AdaClass("one"),
 *   new AdaClass("two")
 * ]
 */
class AdaClassService
{
    /** @var HttpApiClientFactory */
    private $clientFactory;

    /** @var string */
    private $baseUrl;

    /** @var AdaClassMapper */
    private $mapper;

    public function __construct(
        HttpApiClientFactory $clientFactory,
        string $baseUrl,
        AdaClassMapper $mapper
    ) {
        $this->clientFactory = $clientFactory;
        $this->baseUrl = $baseUrl;
        $this->mapper = $mapper;
    }

    /**
     * @param Programme $programme
     * @param bool $countWithinTleo
     * @param int $limit
     * @return PromiseInterface (Promise return AdaClass[] when unwrapped)
     */
    public function findRelatedClassesByContainer(
        Programme $programme,
        bool $countWithinTleo = true,
        int $limit = 5
    ): PromiseInterface {
        $client = $this->makeClient($programme, $countWithinTleo, $limit);
        return $client->makeCachedPromise();
    }

    private function makeClient(Programme $programme, bool $countWithinTleo, int $limit): HttpApiMultiClient
    {
        // If $countWithinTleo is true, then the programme_item_count returned
        // shall be the number of items with a tag WITHIN the TLEO.
        // If $countWithinTleo is false, then the programme_item_count returned
        // shall be the number of items across the entire BBC
        $contextPid = ($countWithinTleo ? (string) $programme->getTleo()->getPid() : null);

        $stringPid = (string) $programme->getPid();

        // Request twice as many items as the desired limit becase we may remove
        // some classes due to duplication (see class comment)
        $url = $this->buildRequestUrl($stringPid, $contextPid, null, null, 2, 'rank', 'descending', $limit * 2);

        $cacheKey = $this->clientFactory->keyHelper(__CLASS__, __FUNCTION__, $stringPid, $countWithinTleo);

        return $this->clientFactory->getHttpApiMultiClient(
            $cacheKey,
            [$url],
            Closure::fromCallable([$this, 'parseResponse']),
            [$contextPid, $limit],
            [],
            CacheInterface::NORMAL,
            CacheInterface::SHORT,
            [
                'timeout' => 10,
            ]
        );
    }

    private function buildRequestUrl(
        ?string $programmePid = null,
        ?string $contextPid = null,
        ?string $countItemType = null,
        ?string $type = null,
        ?int $threshold = null,
        ?string $order = null,
        ?string $orderDirection = 'descending',
        int $limit = 10,
        int $page = 1
    ):string {
        $params = http_build_query(
            [
                'page' => $page,
                'page_size' => $limit,
                'programme' => $programmePid,
                'count_item_type' => $countItemType,
                'type' => $type,
                'count_context' => $contextPid,
                'threshold' => $threshold,
                'order' => $order,
                'direction' => $orderDirection,
            ]
        );
        return $this->baseUrl . '/classes?' . $params;
    }

    /**
     * @param Response[] $responses
     * @param null|string $countContextPid
     * @param int $limit
     * @return AdaClass[]
     */
    private function parseResponse(array $responses, ?string $countContextPid, int $limit): array
    {
        $data = json_decode($responses[0]->getBody()->getContents(), true);
        if (!isset($data['items'])) {
            throw new MultiParseException(0, "Ada JSON response does not contain items element");
        }

        $classes = [];
        foreach ($data['items'] as $item) {
            $classes[] = $this->mapper->mapItem($item, $countContextPid);
        }

        return array_slice($this->deduplicateClasses($classes), 0, $limit);
    }

    /**
     * Classes that are returned from the API may be of type "tag" or "category"
     * however in our listings we do not differentiate between these two. Thus
     * if ADA returns a list of classes that contain a tag and a category with
     * the same id we should remove one of them to avoid it looking like there
     * are duplicate items in the list.
     *
     * @param AdaClass[] $classes
     * @return AdaClass[]
     */
    private function deduplicateClasses(array $classes): array
    {
        $uniqueIds = [];
        $uniqueClasses = [];

        foreach ($classes as $class) {
            if (!array_key_exists($class->getId(), $uniqueIds)) {
                $uniqueIds[$class->getId()] = true;
                $uniqueClasses[] = $class;
            }
        }
        return $uniqueClasses;
    }
}
