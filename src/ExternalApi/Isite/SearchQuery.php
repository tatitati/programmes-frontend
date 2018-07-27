<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite;

use InvalidArgumentException;
use stdClass;

class SearchQuery implements QueryInterface
{
    private const MAX_PAGE_SIZE = 48;

    /** @var stdClass */
    private $q;

    public function __construct()
    {
        $this->q = new stdClass();
    }

    public function setProject(string $project): self
    {
        $this->q->project = $project;
        return $this;
    }

    public function setNamespace(string $project): self
    {
        $this->q->namespaces = new stdClass();
        $this->q->namespaces->profile = 'https://production.bbc.co.uk/isite2/project/' . $project . '/programmes-profile';

        return $this;
    }

    /**
     * Set which page of results to return.  Not setting this will result in all results being fetched
     */
    public function setPage(int $pageNumber): self
    {
        $this->q->page = (string) $pageNumber;

        return $this;
    }

    public function setPageSize(int $resultsPerPage): self
    {
        if ($resultsPerPage > self::MAX_PAGE_SIZE || $resultsPerPage < 0) {
            throw new InvalidArgumentException('$resultsPerPage must be between 0 and ' . self::MAX_PAGE_SIZE);
        }

        $this->q->pageSize = (string) $resultsPerPage;

        return $this;
    }

    public function setQuery(array $query): self
    {
        $this->q->query = $query;

        return $this;
    }

    /**
     * @param string[][] $sort
     * @return SearchQuery
     */
    public function setSort(array $sort): self
    {
        $this->q->sort = $sort;

        return $this;
    }

    public function setDepth(int $depth): self
    {
        $this->q->depth = $depth;

        return $this;
    }

    public function getPath(): string
    {
        return '/search?' . http_build_query(['q' => json_encode($this->q)]);
    }
}
