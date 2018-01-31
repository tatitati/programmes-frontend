<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Utilities\Paginator;

use App\Ds2013\Presenter;

class PaginatorPresenter extends Presenter
{
    /** @var int */
    private $currentPage;

    /** @var int */
    private $pageSize;

    /** @var int */
    private $totalItems;

    /** @var (int|string)[] */
    private $items;

    public function __construct(int $currentPage, int $pageSize, int $totalItems, array $options = [])
    {
        parent::__construct($options);
        $this->currentPage = $currentPage;
        $this->pageSize = $pageSize;
        $this->totalItems = $totalItems;

        $this->items = $this->buildItems();
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getOffset(int $item): int
    {
        return abs($item - $this->currentPage);
    }

    public function getPageCount(): int
    {
        return (int) ceil($this->totalItems / $this->pageSize);
    }

    /**
     * This method builds the pagination items that are going to be shown.
     * This gets quite complicated around the 7, 8 and 9 page mark.
     * For example, even though we want 2 pages either side of the current page, if we are on page 5 we don't want:
     * 1 ... 3 4 5 6 7 ...
     * The ellipsis in this case is pointless. We actually want:
     * 1 2 3 4 5 6 7 8
     * See the test for this class for more examples
     *
     * @return array
     */
    private function buildItems(): array
    {
        $pages = $this->getPageCount();
        if ($this->shouldShowAllPages()) {
            return range(1, $pages);
        }

        if ($this->spacerAtStartOnly()) {
            $numberOfPagesAfterSpacer = max(5, ($pages - $this->currentPage) + 3);
            return array_merge([1, 'spacer'], range($pages - ($numberOfPagesAfterSpacer - 1), $pages));
        }

        if ($this->spacerAtEndOnly()) {
            $numberOfPagesBeforeSpacer = max(5, $this->currentPage + 2);
            return array_merge(range(1, $numberOfPagesBeforeSpacer), ['spacer', $pages]);
        }

        return array_merge([1, 'spacer'], range($this->currentPage - 2, $this->currentPage + 2), ['spacer', $pages]);
    }

    /**
     * Does the list of pages need any spacers (ellipsis)?
     *
     * @return bool
     */
    private function shouldShowAllPages(): bool
    {
        $totalPages = $this->getPageCount();
        if ($totalPages === 8 && ($this->currentPage === 4 || $this->currentPage === 5)) {
            return true;
        }

        if ($totalPages === 9 && $this->currentPage === 5) {
            return true;
        }

        return $totalPages <= 7;
    }

    private function spacerAtEndOnly(): bool
    {
        return $this->currentPage <= 5 && !$this->shouldShowAllPages();
    }

    private function spacerAtStartOnly(): bool
    {
        return $this->currentPage >= $this->getPageCount() - 4 && !$this->shouldShowAllPages();
    }
}
